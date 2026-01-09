<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkOrderInstall extends Model
{
    use HasFactory;
    protected $fillable = [
        'no_spk',
        'survey_id',
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

        'tanggal_rfs',
        'durasi',
        'nama_durasi',
        'harga_sewa',
        'harga_instalasi',
        'admin_id',
        'status',
        'keterangan',
        'non_stock'
    ];
    public function survey()
    {
        return $this->belongsTo(WorkOrderSurvey::class, 'survey_id');
    }
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
    // Relasi ke RequestBarangStockBarang (Barang yang direquest)
    public function WorkOrderInstallDetail()
    {
        return $this->hasMany(WorkOrderInstallDetail::class, 'work_order_install_id');
    }
    public function barangKeluar()
    {
        return $this->hasMany(BarangKeluar::class, 'work_order_install_id');
    }

    public function beritaAcara()
    {
        return $this->hasOne(BeritaAcara::class, 'work_order_install_id');
    }
}
