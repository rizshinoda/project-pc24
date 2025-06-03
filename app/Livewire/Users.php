<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;
use App\Models\Conversation;

class Users extends Component
{

    public function message($userId)
    {
        $authenticatedUser = auth()->user();
        $authenticatedUserId = $authenticatedUser->id;

        // Cek apakah sudah ada percakapan sebelumnya
        $existingConversation = Conversation::where(function ($query) use ($authenticatedUserId, $userId) {
            $query->where('sender_id', $authenticatedUserId)
                ->where('receiver_id', $userId);
        })->orWhere(function ($query) use ($authenticatedUserId, $userId) {
            $query->where('sender_id', $userId)
                ->where('receiver_id', $authenticatedUserId);
        })->first();

        // Tentukan route berdasarkan role
        $chatRoute = match ($authenticatedUser->is_role) {
            0 => 'superadmin.chat',  // Super Admin (0) & Admin (1)
            1 => 'admin.chat',  // Super Admin (0) & Admin (1)
            2 => 'ga.chat',        // GA (2)
            3 => 'helpdesk.chat',  // Helpdesk (3)
            4 => 'noc.chat',
            5 => 'psb.chat',
            6 => 'na.chat',
            default => abort(403, 'Unauthorized action.'),
        };

        // Jika percakapan sudah ada, redirect ke chat
        if ($existingConversation) {
            return redirect()->route($chatRoute, ['query' => $existingConversation->id]);
        }

        // Jika belum ada, buat percakapan baru
        $createdConversation = Conversation::create([
            'sender_id' => $authenticatedUserId,
            'receiver_id' => $userId
        ]);

        return redirect()->route($chatRoute, ['query' => $createdConversation->id]);
    }


    public function render()
    {
        return view('livewire.users', ['users' => User::where('id', '!=', auth()->id())->get()]);
    }
}
