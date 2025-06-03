<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Instansi extends Model
{
    use HasFactory;
    protected $table = 'instansis'; // Nama tabel di database

    protected $fillable = [
        'nama_instansi',
    ];

    public function surveys()
    {
        return $this->hasMany(WorkOrderSurvey::class, 'instansi_id');
    }
}
