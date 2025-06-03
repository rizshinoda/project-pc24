<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UpgradeProgress extends Model
{
    use HasFactory;
    protected $fillable = [
        'work_order_upgrade_id',
        'keterangan',
        'foto',
    ];

    public function upgrade()
    {
        return $this->belongsTo(WorkOrderUpgrade::class);
    }

    // Relasi ke foto progress
    public function photos()
    {
        return $this->hasMany(UpgradeProgressPhoto::class, 'upgrade_progress_id');
    }

    public function userPSB()
    {
        return $this->belongsTo(User::class, 'psb_id');
    }
}
