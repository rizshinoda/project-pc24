<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceProgress extends Model
{
    use HasFactory;
    protected $fillable = [
        'work_order_maintenance_id',
        'keterangan',
        'foto',
    ];

    public function maintenance()
    {
        return $this->belongsTo(WorkOrderMaintenance::class);
    }

    // Relasi ke foto progress
    public function photos()
    {
        return $this->hasMany(MaintenanceProgressPhoto::class, 'maintenance_progress_id');
    }

    public function userPSB()
    {
        return $this->belongsTo(User::class, 'psb_id');
    }
}
