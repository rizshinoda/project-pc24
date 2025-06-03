<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UpgradeProgressPhoto extends Model
{
    use HasFactory;
    protected $fillable = ['upgrade_progress_id', 'file_path'];

    public function progress()
    {
        return $this->belongsTo(UpgradeProgress::class);
    }
}
