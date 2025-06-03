<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReqBarangProgress extends Model
{
    use HasFactory;
    protected $fillable = [
        'req_barang_id',
        'keterangan',
        'foto',
    ];
    public function requestbarang()
    {
        return $this->belongsTo(RequestBarang::class);
    }

    // Relasi ke foto progress
    public function photos()
    {
        return $this->hasMany(ReqBarangProgressPhoto::class, 'reqbarang_progress_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
