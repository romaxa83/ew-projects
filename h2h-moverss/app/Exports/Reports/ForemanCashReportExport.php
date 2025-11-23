<?php

namespace App\Exports\Reports;

use App\Http\Controllers\Reports\ForemanCashReportController;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ForemanCashReportExport implements FromArray, WithHeadings, WithMapping
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function headings(): array
    {
        return ForemanCashReportController::fileHeaders();
    }

    public function map($data): array
    {
        dd($data);
        return array_values($data);
    }

    public function array(): array
    {
        return $this->data;
    }
}
