<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockBarang extends Model
{
    use HasFactory;

    protected $table = 'stock_barangs';

    protected $fillable =
    [
        'jenis_id',
        'merek_id',
        'tipe_id',
        'serial_number',
        'jumlah',
        'kualitas',
        'dismantle_id', // Kolom relasi ke dismantle

    ];
    // Relasi ke RequestBarangStockBarang
    public function requestBarangDetails()
    {
        return $this->hasMany(RequestBarangDetails::class, 'stock_barang_id');
    }

    public function jenis()
    {
        return $this->belongsTo(Jenis::class, 'jenis_id');
    }

    public function merek()
    {
        return $this->belongsTo(Merek::class, 'merek_id'); // Pastikan 'merek_id' adalah kolom yang tepat
    }

    public function tipe()
    {
        return $this->belongsTo(Tipe::class, 'tipe_id'); // Pastikan 'tipe_id' adalah kolom yang tepat
    }
    public function barangKeluars()
    {
        return $this->hasMany(BarangKeluar::class);
    }
    // Di dalam model StockBarang.php
    public function requestBarang()
    {
        return $this->belongsTo(RequestBarang::class);
    }

    // Relasi ke RequestBarangStockBarang
    public function WorkOrderInstallDetail()
    {
        return $this->hasMany(WorkOrderInstallDetail::class, 'stock_barang_id');
    }
    // Di dalam model StockBarang.php
    public function WorkOrderInstall()
    {
        return $this->belongsTo(WorkOrderInstall::class);
    }
    public function dismantle()
    {
        return $this->belongsTo(WorkOrderDismantle::class, 'dismantle_id');
    }
}
