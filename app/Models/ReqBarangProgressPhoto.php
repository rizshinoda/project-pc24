<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReqBarangProgressPhoto extends Model
{
    use HasFactory;
    protected $fillable = [
        'reqbarang_progress_id',
        'file_path'
    ];
    public function progress()
    {
        return $this->belongsTo(ReqBarangProgress::class);
    }
}
