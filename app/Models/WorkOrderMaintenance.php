<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkOrderMaintenance extends Model
{
    use HasFactory;
    protected $fillable = [
        'online_billing_id',
        'no_spk',
        'status',
        'keterangan',
        'admin_id',

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
    public function WorkOrderMaintenanceDetail()
    {
        return $this->hasMany(WorkOrderMaintenanceDetail::class, 'work_order_maintenance_id');
    }
    public function barangKeluar()
    {
        return $this->hasMany(BarangKeluar::class, 'work_order_maintenance_id');
    }
}
