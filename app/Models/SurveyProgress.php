<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurveyProgress extends Model
{
    use HasFactory;
    protected $fillable = [
        'work_order_survey_id',
        'keterangan',
        'foto',
    ];

    public function survey()
    {
        return $this->belongsTo(WorkOrderSurvey::class);
    }

    // Relasi ke foto progress
    public function photos()
    {
        return $this->hasMany(SurveyProgressPhoto::class, 'survey_progress_id');
    }

    public function userPSB()
    {
        return $this->belongsTo(User::class, 'psb_id');
    }
}
