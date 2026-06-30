<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkOrderDowngrade extends Model
{
    use HasFactory;
    protected $fillable = [
        'online_billing_id',
        'no_spk',
        'bandwidth_baru',
        'satuan',
        'status',
        'keterangan',

        'admin_id',
        'attachments', // tambahkan ini supaya bisa diisi massal
        'tanggal_rfs'

    ];
    protected $casts = [
        'attachments' => 'array', // penting supaya JSON di DB otomatis jadi array di PHP
    ];
    /**
     * Relationship with the OnlineBilling model.
     */
    public function onlineBilling()
    {
        return $this->belongsTo(OnlineBilling::class, 'online_billing_id');
    }
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
    public function statuses()
    {
        return $this->morphMany(Status::class, 'workOrderable');
    }
    public function beritaAcara()
    {
        return $this->morphOne(
            BeritaAcara::class,
            'workOrder',
            'work_order_type',
            'work_order_id'
        );
    }
}
