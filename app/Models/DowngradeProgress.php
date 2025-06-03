<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DowngradeProgress extends Model
{
    use HasFactory;
    protected $fillable = [
        'work_order_downgrade_id',
        'keterangan',
        'foto',
    ];

    public function downgrade()
    {
        return $this->belongsTo(WorkOrderDowngrade::class);
    }

    // Relasi ke foto progress
    public function photos()
    {
        return $this->hasMany(DowngradeProgressPhoto::class, 'downgrade_progress_id');
    }

    public function userPSB()
    {
        return $this->belongsTo(User::class, 'psb_id');
    }
}
