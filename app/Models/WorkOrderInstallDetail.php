<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkOrderInstallDetail extends Model
{
    use HasFactory;
    protected $fillable = [
        'work_order_install_id',
        'stock_barang_id',
        'merek',
        'tipe',
        'kualitas',
        'jumlah'
    ];

    // Relasi ke tabel request_barangs
    public function WorkOrderInstall()
    {
        return $this->belongsTo(WorkOrderInstall::class);
    }

    // Relasi ke tabel stock_barangs
    public function stockBarang()
    {
        return $this->belongsTo(StockBarang::class, 'stock_barang_id');
    }
}
