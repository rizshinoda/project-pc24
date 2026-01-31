<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DismantleDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'dismantle_id',
        'jenis_id',
        'merek_id',
        'tipe_id',
        'kualitas',
        'serial_number',
        'jumlah',
    ];

    public function jenis()
    {
        return $this->belongsTo(Jenis::class);
    }

    public function merek()
    {
        return $this->belongsTo(Merek::class);
    }

    public function tipe()
    {
        return $this->belongsTo(Tipe::class);
    }

    public function dismantle()
    {
        return $this->belongsTo(WorkOrderDismantle::class, 'dismantle_id');
    }
}
