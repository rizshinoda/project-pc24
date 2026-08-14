<?php

namespace App\Exports;

use App\Exports\EmptyOnlineBillingSheet;
use App\Exports\OnlineBillingPerPelangganSheet;
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
    protected $onlineBillingIds;

    public function __construct(array $onlineBillingIds)
    {
        $this->onlineBillingIds = $onlineBillingIds;
    }

    public function sheets(): array
    {
        $sheets = [];

        /*
        |--------------------------------------------------------------------------
        | Tidak ada data
        |--------------------------------------------------------------------------
        */

        if (empty($this->onlineBillingIds)) {
            return [
                new EmptyOnlineBillingSheet()
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Ambil Online Billing hasil filter
        |--------------------------------------------------------------------------
        */

        $onlineBillings = OnlineBilling::whereIn(
            'id',
            $this->onlineBillingIds
        )
            ->with([
                'pelanggan',
                'instansi',
                'vendor',
                'admin',
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Kelompokkan berdasarkan pelanggan
        |--------------------------------------------------------------------------
        */

        $pelangganIds = $onlineBillings
            ->pluck('pelanggan_id')
            ->filter()
            ->unique()
            ->values();

        $pelanggans = Pelanggan::whereIn(
            'id',
            $pelangganIds
        )
            ->orderBy('nama_pelanggan')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Buat sheet
        |--------------------------------------------------------------------------
        */

        foreach ($pelanggans as $pelanggan) {

            $sheets[] = new OnlineBillingPerPelangganSheet(
                $pelanggan,
                $this->onlineBillingIds
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Pengaman
        |--------------------------------------------------------------------------
        */

        if (empty($sheets)) {
            return [
                new EmptyOnlineBillingSheet()
            ];
        }

        return $sheets;
    }
}
