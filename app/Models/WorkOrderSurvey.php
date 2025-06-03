<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkOrderSurvey extends Model
{
    use HasFactory;

    protected $fillable = [
        'no_spk',
        'admin_id',
        'status',
        'pelanggan_id',
        'vendor_id',
        'instansi_id',
        'nama_site',
        'alamat_pemasangan',
        'nama_pic',
        'no_pic',
        'layanan',
        'media',
        'bandwidth',
        'satuan',
        'nni',
        'provinsi',
        'vlan',
        'no_jaringan',
        'tanggal_rfs',

    ];
    // const STATUS_PENDING = 'Pending';
    // const STATUS_IN_PROGRESS = 'In Progress';
    // const STATUS_COMPLETED = 'Completed';
    // const STATUS_CANCELED = 'Canceled';

    // public function getStatusList()
    // {
    //     return [
    //         self::STATUS_PENDING,
    //         self::STATUS_IN_PROGRESS,
    //         self::STATUS_COMPLETED,
    //         self::STATUS_CANCELED,
    //     ];
    // }
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'pelanggan_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function instansi()
    {
        return $this->belongsTo(Instansi::class, 'instansi_id');
    }
}
