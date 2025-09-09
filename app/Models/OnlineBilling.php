<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OnlineBilling extends Model
{
    use HasFactory;
    protected $fillable = [
        'work_order_install_id',
        'pelanggan_id',
        'instansi_id',
        'vendor_id',
        'nama_site',
        'alamat_pemasangan',
        'nama_pic',
        'no_pic',
        'layanan',
        'media',
        'bandwidth',
        'provinsi',
        'satuan',
        'nni',
        'vlan',
        'no_jaringan',
        'tanggal_instalasi',
        'tanggal_mulai',
        'tanggal_akhir',
        'durasi',
        'nama_durasi',
        'harga_sewa',
        'admin_id',
        'status',
        'sid_vendor',
        'cacti_link'
    ];

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
    public function workOrderUpgrades()
    {
        return $this->hasMany(WorkOrderUpgrade::class, 'online_billing_id');
    }
    public function workOrderDowngrades()
    {
        return $this->hasMany(WorkOrderDowngrade::class, 'online_billing_id');
    }
    public function workOrderDismantles()
    {
        return $this->hasMany(workOrderDismantle::class, 'online_billing_id');
    }
}
