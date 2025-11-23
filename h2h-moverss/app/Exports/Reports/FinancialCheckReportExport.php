<?php

namespace App\Exports\Reports;

use App\Http\Controllers\Reports\FinancialCheckController;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class FinancialCheckReportExport implements FromArray, WithHeadings, WithMapping
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function headings(): array
    {
        return FinancialCheckController::fileHeaders();
    }

    public function map($data): array
    {
        return array_values($data);
    }

    public function array(): array
    {
        return $this->data;
    }
}
