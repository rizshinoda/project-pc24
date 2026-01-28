<?php

namespace App\Exports;

use App\Models\OnlineBilling;
use App\Models\WorkOrderDismantle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;

class OnlineBillingExport extends StringValueBinder implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithColumnWidths,
    WithStyles,
    WithColumnFormatting,
    WithCustomValueBinder
{

    private $index = 0; // Untuk nomor urut otomatis

    /**
     * Mengambil data dari database
     */
    public function collection()
    {
        return OnlineBilling::all();
    }

    /**
     * Mapping data yang akan diexport
     */
    public function map($workOrder): array
    {
        return [
            ++$this->index,
            $workOrder->pelanggan->nama_pelanggan ?? '-',
            $workOrder->no_jaringan ?? '-',
            $workOrder->instansi->nama_instansi ?? '-',
            $workOrder->nama_site ?? '-',
            $workOrder->alamat_pemasangan ?? '-',
            $workOrder->nama_pic ?? '-',
            $workOrder->no_pic ?? '-',
            $workOrder->layanan ?? '-',
            $workOrder->media ?? '-',
            $workOrder->bandwidth ?? '-',
            $workOrder->satuan ?? '-',
            $workOrder->provinsi ?? '-',
            $workOrder->nni ?? '-',
            $workOrder->vlan ?? '-',
            $workOrder->tanggal_instalasi ?? '-',
            $workOrder->tanggal_mulai ?? '-',
            $workOrder->tanggal_akhir ?? '-',
            $workOrder->durasi ?? '-',
            $workOrder->nama_durasi ?? '-',
            $workOrder->vendor->nama_vendor ?? '-',

            // 🔥 SID Vendor dipaksa STRING (meski DB sudah string)
            '' . $workOrder->sid_vendor,
        ];
    }

    /**
     * Format kolom
     */
    public function columnFormats(): array
    {
        return [
            'V' => NumberFormat::FORMAT_TEXT, // SID Vendor
        ];
    }

    /**
     * Header
     */
    public function headings(): array
    {
        return [
            'No',
            'Nama Pelanggan',
            'No Jaringan',
            'Nama Instansi',
            'Nama Site',
            'Alamat Pemasangan',
            'Nama PIC',
            'No PIC',
            'Layanan',
            'Media',
            'Bandwidth',
            'Satuan',
            'Provinsi',
            'NNI',
            'Vlan',
            'Tanggal Instalasi',
            'Tanggal Mulai',
            'Tanggal Akhir',
            'Durasi',
            'Nama Durasi',
            'Nama Vendor',
            'SID Vendor',
        ];
    }

    /**
     * Lebar kolom
     */
    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 35,
            'C' => 35,
            'D' => 25,
            'E' => 45,
            'F' => 50,
            'G' => 20,
            'H' => 20,
            'I' => 20,
            'J' => 20,
            'K' => 15,
            'L' => 15,
            'M' => 30,
            'N' => 30,
            'O' => 15,
            'P' => 18,
            'Q' => 18,
            'R' => 18,
            'S' => 15,
            'T' => 18,
            'U' => 30,
            'V' => 20,
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
        $sheet->getStyle('A1:V1')->applyFromArray([
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
        $sheet->getStyle("A1:V$lastRow")->getAlignment()->setWrapText(true);

        // Terapkan border ke seluruh tabel (dari A1 sampai L[lastRow])
        $sheet->getStyle("A1:V$lastRow")->applyFromArray([
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
