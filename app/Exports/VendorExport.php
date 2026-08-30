<?php

namespace App\Exports;

use App\Models\Vendor;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class VendorExport implements FromCollection, WithHeadings, WithMapping, WithColumnWidths, WithStyles
{

    private $index = 0; // Untuk nomor urut otomatis

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Vendor::all();
    }

    public function map($workOrder): array
    {
        return [
            ++$this->index, // Nomor otomatis
            $workOrder->nama_vendor,
            $workOrder->contact ?? '-', // Ambil nama instansi

        ];
    }

    /**
     * Header kolom pada Excel
     */
    public function headings(): array
    {
        return [

            'No',  // Header untuk nomor urut
            'Nama Vendor',
            'Contact',

        ];
    }

    /**
     * Mengatur lebar setiap kolom
     */
    public function columnWidths(): array
    {
        return [
            'A' => 5,   // No
            'B' => 35,  // No SPK
            'C' => 15,  // Nama Pelanggan

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
        $sheet->getStyle('A1:C1')->applyFromArray([
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
        $sheet->getStyle("A1:C$lastRow")->getAlignment()->setWrapText(true);

        // Terapkan border ke seluruh tabel (dari A1 sampai L[lastRow])
        $sheet->getStyle("A1:C$lastRow")->applyFromArray([
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
