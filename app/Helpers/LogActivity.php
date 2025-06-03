<?php

namespace App\Helpers;

use App\Models\LogActivity as LogModel;
use Illuminate\Support\Facades\Auth;

class LogActivity
{
    public static function add($title, $description = null, $action = 'create')
    {
        \App\Models\LogActivity::create([
            'title' => $title,
            'description' => $description,
            'action' => $action,
            'user_id' => Auth::id(),
        ]);
    }
}
