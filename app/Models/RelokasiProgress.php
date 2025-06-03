<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RelokasiProgress extends Model
{
    use HasFactory;
    protected $fillable = [
        'work_order_relokasi_id',
        'keterangan',
        'foto',
    ];

    public function relokasi()
    {
        return $this->belongsTo(WorkOrderRelokasi::class);
    }

    // Relasi ke foto progress
    public function photos()
    {
        return $this->hasMany(RelokasiProgressPhoto::class, 'relokasi_progress_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'psb_id');
    }
}
