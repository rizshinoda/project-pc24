<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id', // Menambahkan user_id di sini
        'url',      // URL terkait notifikasi
        'message',
        'is_read',
    ];
}
