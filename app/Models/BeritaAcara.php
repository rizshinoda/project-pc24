<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BeritaAcara extends Model
{
    use HasFactory;
    protected $fillable = [
        'work_order_install_id',
        'user_id',
        'tanggal_kirim',
        'tanggal_terima',
        'status',
    ];
    // Pastikan atribut tanggal dikonversi ke objek Carbon
    protected $casts = [
        'tanggal_kirim' => 'datetime',
        'tanggal_terima' => 'datetime',
    ];
    // Relasi ke tabel Work Order Install
    public function workOrderInstall()
    {
        return $this->belongsTo(WorkOrderInstall::class, 'work_order_install_id');
    }

    // Relasi ke tabel User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
