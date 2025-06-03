<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RelokasiProgressPhoto extends Model
{
    use HasFactory;
    protected $fillable = ['relokasi_progress_id', 'file_path'];

    public function progress()
    {
        return $this->belongsTo(RelokasiProgress::class);
    }
}
