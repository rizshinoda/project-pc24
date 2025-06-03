<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GantiVendorProgress extends Model
{
    use HasFactory;
    protected $fillable = [
        'work_order_ganti_vendor_id',
        'keterangan',
        'foto',
    ];

    public function gantivendor()
    {
        return $this->belongsTo(WorkOrderGantiVendor::class);
    }

    // Relasi ke foto progress
    public function photos()
    {
        return $this->hasMany(GantiVendorProgressPhoto::class, 'ganti_vendor_progress_id');
    }

    public function userPSB()
    {
        return $this->belongsTo(User::class, 'psb_id');
    }
}
