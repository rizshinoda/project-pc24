<?php

namespace App\Imports;

use App\Models\Vendor;
use App\Models\Instansi;
use App\Models\Pelanggan;
use App\Models\OnlineBilling;
use App\Models\WorkOrderInstall;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToModel;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class OnlineBillingImport implements ToModel, WithHeadingRow, WithChunkReading

{

    // ───────────────────── CHUNK SIZE (efisiensi) ─────────────────────
    public function chunkSize(): int
    {
        return 1000;
    }
    public function headingRow(): int
    {
        return 1; // karena header dimulai di baris ke-4
    }
    // ───────────────────── VALIDASI PER BARIS ─────────────────────────
    private function excelDateToDate($excelDate)
    {
        if (is_numeric($excelDate)) {
            $unixDate = ($excelDate - 25569) * 86400;
            return gmdate("Y-m-d", $unixDate);
        }
        return null;
    }

    // ───────────────────── KONVERSI BARIS → MODEL ─────────────────────
    public function model(array $row)
    {

        // Pastikan key ada dan nilainya tidak kosong
        if (!isset($row['nama_pelanggan']) || empty(trim($row['nama_pelanggan']))) {
            // skip baris ini, jangan proses
            return null;
        }
        $pelanggan = Pelanggan::firstOrCreate(['nama_pelanggan' => $row['nama_pelanggan']]);
        $vendor = vendor::firstOrCreate(['nama_vendor' => $row['nama_vendor']]);
        /* 2.  Work‑order: kolom boleh kosong */
        $workOrderId = null;
        $instansi = null;

        /* 3.  Simpan OnlineBilling */
        return new OnlineBilling([
            'work_order_install_id' => $workOrderId,      // nullable
            'pelanggan_id' => $pelanggan->id,
            'vendor_id'    => $vendor->id,
            'instansi_id'  => $instansi,

            'nama_site'         => $row['nama_perusahaan'],
            'alamat_pemasangan' => $row['alamat'] ?? null,
            'nama_pic'          => $row['nama_pic'] ?? null,
            'no_pic'            => $row['no_pic'] ?? null,
            'layanan'           => $row['produk'] ?? null,
            'media'             => $row['media'],
            'bandwidth'         => $row['vol'],
            'provinsi'          => $row['provinsi'],
            'satuan'            => $row['sat'],
            'nni'               => $row['nni'] ?? null,
            'vlan'              => $row['vlan'] ?? null,
            'no_jaringan'       => $row['sir'] ?? null,
            'pelanggan_id' => $pelanggan->id,
            'tanggal_mulai' => isset($row['tanggal_mulai']) ? $this->excelDateToDate($row['tanggal_mulai']) : null,
            'tanggal_akhir' => isset($row['tanggal_akhir']) ? $this->excelDateToDate($row['tanggal_akhir']) : null,
            'tanggal_instalasi' => isset($row['tanggal_instalasi']) ? $this->excelDateToDate($row['tanggal_instalasi']) : null,
            'durasi'            => $row['durasi'] ?? null,
            'nama_durasi'       => $row['nama_durasi'] ?? null,
            'harga_sewa'        => $row['bulanan'] ?? null,
            'sid_vendor'        => $row['sid_vendor'] ?? null,
            'admin_id'          => Auth::id() ?? 1,
            'status'            => $row['status'] ?? 'active',
        ]);
    }
}
