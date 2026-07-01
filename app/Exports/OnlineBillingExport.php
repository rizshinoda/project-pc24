<?php

namespace App\Exports;

use App\Models\OnlineBilling;
use App\Models\Pelanggan;
use App\Models\WorkOrderDismantle;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OnlineBillingExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        $sheets = [];

        $pelanggans = Pelanggan::whereHas('onlineBillings')->get();

        foreach ($pelanggans as $pelanggan) {
            $sheets[] = new OnlineBillingPerPelangganSheet($pelanggan);
        }

        return $sheets;
    }
}
