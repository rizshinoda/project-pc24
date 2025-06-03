<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstallProgress extends Model
{
    use HasFactory;
    protected $fillable = [
        'work_order_install_id',
        'keterangan',
        'foto',
    ];
    public function getInstall()
    {
        return $this->belongsTo(WorkOrderInstall::class);
    }

    // Relasi ke foto progress
    public function photos()
    {
        return $this->hasMany(InstallProgressPhoto::class, 'install_progress_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
