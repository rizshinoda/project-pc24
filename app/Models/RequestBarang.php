<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestBarang extends Model
{
    use HasFactory;

    protected $table = 'request_barangs';

    protected $fillable = [
        'nama_penerima',
        'alamat_penerima',
        'no_penerima',
        'keterangan',
        'status',
        'user_id',
    ];

    // Relasi ke user (User yang melakukan request)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke RequestBarangStockBarang (Barang yang direquest)
    public function requestBarangDetails()
    {
        return $this->hasMany(RequestBarangDetails::class, 'request_barang_id');
    }
    public function barangKeluar()
    {
        return $this->hasMany(BarangKeluar::class, 'request_barang_id');
    }
}
