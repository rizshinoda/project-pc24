<?php

namespace App\Exports;

use App\Models\WorkOrderInstall;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class WorkOrderInstallExport implements FromCollection, WithHeadings, WithMapping, WithColumnWidths, WithStyles
{
    private $index = 0; // Untuk nomor urut otomatis

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return WorkOrderInstall::all();
    }

    public function map($workOrder): array
    {
        return [
            ++$this->index, // Nomor otomatis
            $workOrder->no_spk,
            $workOrder->pelanggan->nama_pelanggan ?? '-', // Ambil nama instansi
            $workOrder->created_at->format('Y-m-d'),
            $workOrder->nama_site,
            $workOrder->alamat_pemasangan, // Alamat panjang dibuat wrap text
            $workOrder->no_jaringan ?? '-',
            $workOrder->layanan,
            $workOrder->vendor->nama_vendor ?? '-', // Ambil nama vendor
            $workOrder->bandwidth . ' ' . $workOrder->satuan, // Gabungkan bandwidth & satuan
            $workOrder->tanggal_rfs,
            $workOrder->keterangan ?? '-', // Gabungkan bandwidth & satuan
            $workOrder->status,
        ];
    }

    /**
     * Header kolom pada Excel
     */
    public function headings(): array
    {
        return [

            'No',  // Header untuk nomor urut
            'No SPK',
            'Nama Pelanggan',
            'Tanggal dibuat',
            'Nama Site',
            'Alamat Pemasangan',
            'No Jaringan',
            'Layanan',
            'Vendor',
            'Bandwidth',
            'Tanggal RFS',
            'Keterangan',
            'Status'
        ];
    }

    /**
     * Mengatur lebar setiap kolom
     */
    public function columnWidths(): array
    {
        return [
            'A' => 5,   // No
            'B' => 15,  // No SPK
            'C' => 25,  // Nama Pelanggan
            'D' => 15,  // Tanggal
            'E' => 20,  // Nama Site
            'F' => 50,  // Alamat Pemasangan (dibuat lebih lebar agar wrap text aktif)
            'G' => 15,  // No Jaringan
            'H' => 20,  // Layanan
            'I' => 20,  // Vendor
            'J' => 15,  // Bandwidth
            'K' => 15,  // Tanggal RFS
            'L' => 50,  // Status
            'M' => 15,  // Status

        ];
    }

    /**
     * Mengatur style pada header & isi
     */
    public function styles(Worksheet $sheet)
    {

        // Atur tinggi baris header
        $sheet->getRowDimension(1)->setRowHeight(30);

        // Ambil jumlah baris terakhir berdasarkan jumlah data yang diekspor
        $lastRow = $sheet->getHighestRow();

        // Buat teks di header bold, tengah, dan berwarna kuning
        $sheet->getStyle('A1:M1')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFFF00'] // Warna kuning
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]
            ],
        ]);

        // Buat alamat pemasangan wrap text agar panjang dibuat ke bawah
        $sheet->getStyle("A1:M$lastRow")->getAlignment()->setWrapText(true);

        // Terapkan border ke seluruh tabel (dari A1 sampai L[lastRow])
        $sheet->getStyle("A1:M$lastRow")->applyFromArray([
            'borders' => [
                'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);
    }
}
