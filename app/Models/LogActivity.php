<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogActivity extends Model
{
    use HasFactory;
    protected $table = 'log_activities'; // pastikan nama tabel sesuai di database

    protected $fillable = [
        'action', // tambahkan ini
        'title',
        'description',
        'user_id',
    ];

    // Relasi ke user yang membuat log (opsional, tapi direkomendasikan)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
