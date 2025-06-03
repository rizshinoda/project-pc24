<?php

namespace App\Livewire\Chat;

use App\Models\Message;
use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\MessageImage;
use Livewire\WithFileUploads;
use App\Notifications\MessageRead;
use App\Notifications\MessageSent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class ChatBox extends Component
{
    use WithFileUploads;

    public $selectedConversation;
    public $body;
    public $loadedMessages = [];
    public $paginate_var = 10;
    public $images = []; // Untuk menyimpan file gambar sementara

    protected $listeners = ['loadMore' => 'loadMore'];

    public function getListeners()
    {
        $auth_id = auth()->user()->id;
        return [
            'loadMore',
            "echo-private:users.{$auth_id},.Illuminate\\Notifications\\Events\\BroadcastNotificationCreated" => 'broadcastedNotifications'
        ];
    }

    public function broadcastedNotifications($event)
    {
        if ($event['type'] == MessageSent::class) {
            if ($event['conversation_id'] == $this->selectedConversation->id) {
                $this->dispatch('scroll-bottom');
                $newMessage = Message::with('images')->find($event['message_id']);
                $this->loadedMessages->push($newMessage);
                $newMessage->read_at = now();
                $newMessage->save();

                $this->selectedConversation->getReceiver()
                    ->notify(new MessageRead($this->selectedConversation->id));
            }
        }
    }

    public function loadMore(): void
    {
        $this->paginate_var += 10;
        $this->loadMessages();
        $this->dispatch('update-chat-height');
    }

    public function loadMessages()
    {
        if (!$this->selectedConversation) {
            return;
        }

        $this->loadedMessages = Message::where('conversation_id', $this->selectedConversation->id)
            ->with('images') // Pastikan gambar ikut dimuat
            ->latest()
            ->take($this->paginate_var)
            ->get()
            ->reverse(); // Agar urutannya dari lama ke baru
    }

    public function mount()
    {
        $this->loadMessages();
    }

    public function removeImage($index)
    {
        array_splice($this->images, $index, 1);
    }


    public function sendMessage()
    {
        if (!trim($this->body) && empty($this->images)) {
            return;
        }

        $this->validate([
            'body' => 'nullable|string',
            'images.*' => 'nullable|image|max:2048',
        ]);

        // Simpan pesan dengan enkripsi
        $createdMessage = Message::create([
            'conversation_id' => $this->selectedConversation->id,
            'sender_id' => Auth::id(),
            'receiver_id' => $this->selectedConversation->getReceiver()->id,
            'body' => Crypt::encryptString($this->body), // Enkripsi pesan
        ]);

        // Simpan gambar jika ada
        if (!empty($this->images)) {
            foreach ($this->images as $image) {
                $imagePath = $image->store('chat_images', 'public');
                MessageImage::create([
                    'message_id' => $createdMessage->id,
                    'image_path' => $imagePath,
                ]);
            }
        }

        // Muat ulang pesan dengan gambar
        $createdMessage->load('images');
        $this->loadedMessages->push($createdMessage);

        // Reset inputan
        $this->reset(['body', 'images']);

        // Scroll ke bawah
        $this->dispatch('scroll-bottom');

        // Update percakapan
        $this->selectedConversation->updated_at = now();
        $this->selectedConversation->save();
        $this->dispatch('refresh');

        // Kirim notifikasi ke penerima
        $this->selectedConversation->getReceiver()->notify(
            new MessageSent(Auth()->user(), $createdMessage, $this->selectedConversation, $this->selectedConversation->getReceiver()->id)
        );
    }


    public function render()
    {
        return view('livewire.chat.chat-box');
    }
}
