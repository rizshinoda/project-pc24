<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tipe extends Model
{
    use HasFactory;
    protected $fillable = ['nama_tipe', 'merek_id'];

    public function merek()
    {
        return $this->belongsTo(Merek::class);
    }

    public function stockBarangs()
    {
        return $this->hasMany(StockBarang::class);
    }
}
