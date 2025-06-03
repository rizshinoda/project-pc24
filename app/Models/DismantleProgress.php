<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DismantleProgress extends Model
{
    use HasFactory;
    protected $fillable = [
        'work_order_dismantle_id',
        'keterangan',
        'foto',
    ];

    public function dismantle()
    {
        return $this->belongsTo(WorkOrderDismantle::class);
    }

    // Relasi ke foto progress
    public function photos()
    {
        return $this->hasMany(DismantleProgressPhoto::class, 'dismantle_progress_id');
    }

    public function userPSB()
    {
        return $this->belongsTo(User::class, 'psb_id');
    }
}
