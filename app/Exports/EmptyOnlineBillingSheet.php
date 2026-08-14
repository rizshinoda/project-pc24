<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

class EmptyOnlineBillingSheet implements FromArray, WithTitle
{
    public function array(): array
    {
        return [
            [
                'Tidak ada data Online Billing sesuai filter yang dipilih.'
            ]
        ];
    }

    public function title(): string
    {
        return 'Tidak Ada Data';
    }
}
