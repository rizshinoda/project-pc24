<?php

namespace App\Livewire\Chat;

use Livewire\Component;
use App\Models\Conversation;

class ChatList extends Component
{

    public $selectedConversation;
    public $query;

    #event listteners dari untuk refresh chat list
    protected $listeners = ['refresh' => 'refreshConversations'];

    public function deleteByUser($id)
    {
        $user = auth()->user();
        $userId = $user->id;
        $conversation = Conversation::find(decrypt($id));

        if (!$conversation) {
            return abort(404, 'Percakapan tidak ditemukan.');
        }

        // Tandai pesan sebagai dihapus oleh pengguna
        $conversation->messages()->each(function ($message) use ($userId) {
            if ($message->sender_id === $userId) {
                $message->update(['sender_deleted_at' => now()]);
            } elseif ($message->receiver_id === $userId) {
                $message->update(['receiver_deleted_at' => now()]);
            }
        });

        // Periksa apakah kedua pengguna telah menghapus percakapan
        $receiverAlsoDeleted = $conversation->messages()
            ->where(function ($query) use ($userId) {
                $query->where('sender_id', $userId)
                    ->orWhere('receiver_id', $userId);
            })
            ->where(function ($query) {
                $query->whereNull('sender_deleted_at')
                    ->orWhereNull('receiver_deleted_at');
            })
            ->doesntExist();

        if ($receiverAlsoDeleted) {
            $conversation->forceDelete();
        }

        // Tentukan route sesuai dengan role user
        $chatRoute = match ($user->is_role) {
            0, 1 => 'admin.chat.index',  // Super Admin & Admin
            2 => 'ga.chat.index',        // GA
            3 => 'helpdesk.chat.index',  // Helpdesk
            4 => 'noc.chat.index',
            5 => 'psb.chat.index',
            6 => 'na.chat.index',
            default => abort(403, 'Unauthorized action.'),
        };
        return redirect()->route($chatRoute);
    }

    public function refreshConversations()
    {
        $this->dispatch('refresh');

        $this->render();
    }
    public function render()
    {
        $user = auth()->user();
        return view('livewire.chat.chat-list', [
            'conversations' => $user->conversations()->latest('updated_at')->get()
        ]);
    }
}
