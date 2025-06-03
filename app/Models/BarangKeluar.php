<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarangKeluar extends Model
{
    use HasFactory;
    protected $fillable = [
        'request_barang_id',
        'work_order_install_id',
        'work_order_relokasi_id',
        'work_order_maintenance_id',
        'stock_barang_id',
        'jumlah',
        'serial_number',
        'kualitas',
        'user_id',
        'is_configured', // Tambahkan ini

    ];

    public function stockBarang()
    {
        return $this->belongsTo(StockBarang::class);
    }

    public function requestBarang()
    {
        return $this->belongsTo(RequestBarang::class);
    }
    public function WorkOrderInstall()
    {
        return $this->belongsTo(WorkOrderInstall::class);
    }
    public function WorkOrderRelokasi()
    {
        return $this->belongsTo(WorkOrderRelokasi::class);
    }
    public function WorkOrderMaintenance()
    {
        return $this->belongsTo(WorkOrderMaintenance::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
