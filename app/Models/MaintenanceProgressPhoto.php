<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceProgressPhoto extends Model
{
    use HasFactory;
    protected $fillable = ['maintenance_progress_id', 'file_path'];

    public function progress()
    {
        return $this->belongsTo(MaintenanceProgress::class);
    }
}
