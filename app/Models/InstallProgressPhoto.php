<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstallProgressPhoto extends Model
{
    use HasFactory;
    protected $fillable = [
        'install_progress_id',
        'file_path'
    ];
    public function progress()
    {
        return $this->belongsTo(InstallProgress::class);
    }
}
