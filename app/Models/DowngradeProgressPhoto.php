<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DowngradeProgressPhoto extends Model
{
    use HasFactory;
    protected $fillable = ['downgrade_progress_id', 'file_path'];

    public function progress()
    {
        return $this->belongsTo(DowngradeProgress::class);
    }
}
