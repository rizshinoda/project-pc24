<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use HasFactory;
    protected $table = 'vendors'; // Nama tabel di database

    protected $fillable = [
        'nama_vendor',
        'contact'
    ];

    public function surveys()
    {
        return $this->hasMany(WorkOrderSurvey::class, 'vendor_id');
    }
    public function gantivendor()
    {
        return $this->hasMany(WorkOrderGantiVendor::class, 'vendor_id');
    }
    /**
     * Relasi ke Work Order Instalasi
     */
    public function installs()
    {
        return $this->hasMany(WorkOrderInstall::class, 'vendor_id');
    }

    /**
     * Relasi ke Online Billing
     *
     * Upgrade, Downgrade, Relokasi, Dismantle, dll
     * mengambil vendor melalui Online Billing.
     */
    public function onlineBillings()
    {
        return $this->hasMany(OnlineBilling::class, 'vendor_id');
    }
}
