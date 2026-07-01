<?php

namespace App\Exports;

use App\Models\OnlineBilling;
use App\Models\WorkOrderDismantle;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OnlineBillingPerPelangganSheet extends DefaultValueBinder implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithColumnWidths,
    WithStyles,
    WithColumnFormatting,
    WithCustomValueBinder,
    WithTitle
{
    protected $pelanggan;

    private $index = 0; // Untuk nomor urut otomatis

    /**
     * Mengambil data dari database
     */

    public function __construct($pelanggan)
    {
        $this->pelanggan = $pelanggan;
    }

    public function collection()
    {
        return OnlineBilling::where('pelanggan_id', $this->pelanggan->id)->get();
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

            // pindah ke sini
            is_numeric($workOrder->harga_sewa) ? (float) $workOrder->harga_sewa : 0,

            $workOrder->tanggal_mulai ?? '-',
            $workOrder->tanggal_akhir ?? '-',
            $workOrder->durasi ?? '-',
            $workOrder->nama_durasi ?? '-',
            $workOrder->vendor->nama_vendor ?? '-',
            '' . $workOrder->sid_vendor,
        ];
    }

    /**
     * Force data type
     */
    public function bindValue(Cell $cell, mixed $value): bool
    {
        if ($cell->getColumn() === 'Q' && $cell->getRow() > 1) {
            $cell->setValueExplicit((float) $value, DataType::TYPE_NUMERIC);
            return true;
        }

        if ($cell->getColumn() === 'W' && $cell->getRow() > 1) {
            $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);
            return true;
        }

        return parent::bindValue($cell, $value);
    }

    /**
     * Format kolom
     */
    public function columnFormats(): array
    {
        return [
            'W' => NumberFormat::FORMAT_TEXT,
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
            'Harga Sewa',
            'Tanggal Mulai',
            'Tanggal Akhir',
            'Durasi',
            'Nama Durasi',
            'Nama Vendor',
            'SID Vendor',
        ];
    }

    /**
     * Nama sheet = nama pelanggan
     */
    public function title(): string
    {
        return $this->pelanggan->nama_pelanggan;
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
            'P' => 15,
            'Q' => 20,
            'R' => 18,
            'S' => 18,
            'T' => 15,
            'U' => 18,
            'V' => 30,
            'W' => 20,
        ];
    }

    /**
     * Styling
     */
    public function styles(Worksheet $sheet)
    {
        $lastRow = $sheet->getHighestRow();

        $sheet->getRowDimension(1)->setRowHeight(30);
        $sheet->getStyle("Q2:Q$lastRow")
            ->getNumberFormat()
            ->setFormatCode('[$Rp-421] #,##0');
        $sheet->getStyle('A1:W1')->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFFF00']
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                ]
            ],
        ]);

        $sheet->getStyle("A1:W$lastRow")->getAlignment()->setWrapText(true);

        $sheet->getStyle("A1:W$lastRow")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                ]
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);
    }
}
