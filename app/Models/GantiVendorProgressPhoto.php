<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GantiVendorProgressPhoto extends Model
{
    use HasFactory;
    protected $fillable = ['ganti_vendor_progress_id', 'file_path'];

    public function progress()
    {
        return $this->belongsTo(GantiVendorProgress::class);
    }
}
