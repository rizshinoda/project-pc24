<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkOrderGantiVendor extends Model
{
    use HasFactory;
    protected $fillable = [
        'online_billing_id',
        'no_spk',
        'keterangan',
        'status',
        'vendor_id',
        'sid_baru',
        'admin_id',
        'approved_by'

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
    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
