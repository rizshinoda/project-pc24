<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurveyProgressPhoto extends Model
{
    use HasFactory;
    protected $fillable = ['survey_progress_id', 'file_path'];

    public function progress()
    {
        return $this->belongsTo(SurveyProgress::class);
    }
}
