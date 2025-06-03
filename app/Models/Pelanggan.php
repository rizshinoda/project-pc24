<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pelanggan extends Model
{
    use HasFactory;
    protected $table = 'pelanggans'; // Nama tabel di database

    protected $fillable = [
        'nama_pelanggan',
        'alamat',
        'nama_gedung',
        'no_pelanggan',
        'foto',


    ];

    public function surveys()
    {
        return $this->hasMany(WorkOrderSurvey::class, 'pelanggan_id');
    }
}
