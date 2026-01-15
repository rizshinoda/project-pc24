<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkOrderUpgrade extends Model
{
    use HasFactory;
    protected $fillable = [
        'online_billing_id',
        'no_spk',
        'bandwidth_baru',
        'satuan',
        'status',
        'admin_id',
        'non_stock',
        'keterangan',
        'attachments', // tambahkan ini supaya bisa diisi massal

    ];
    protected $casts = [
        'attachments' => 'array', // penting supaya JSON di DB otomatis jadi array di PHP
    ];
    /**
     * Relationship with the OnlineBilling model.
     */
    public function onlineBilling()
    {
        return $this->belongsTo(OnlineBilling::class, 'online_billing_id');
    }
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
    public function statuses()
    {
        return $this->morphMany(Status::class, 'workOrderable');
    }
    // Relasi ke RequestBarangStockBarang (Barang yang direquest)
    public function WorkOrderUpgradeDetail()
    {
        return $this->hasMany(WorkOrderUpgradeDetail::class, 'work_order_upgrade_id');
    }
    public function barangKeluar()
    {
        return $this->hasMany(BarangKeluar::class, 'work_order_upgrade_id');
    }
}
