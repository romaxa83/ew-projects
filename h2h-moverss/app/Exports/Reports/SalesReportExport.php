<?php

namespace App\Exports\Reports;

use App\Http\Controllers\Reports\SalesReportController;
use App\User;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SalesReportExport implements FromArray, WithHeadings, WithMapping
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function headings(): array
    {
        return SalesReportController::getUsersNameForHeader($this->data);
    }

    public function map($data): array
    {
        unset(
            $data['id'],
            $data['type'],
        );

        return array_values($data);
    }

    public function array(): array
    {
        return $this->data;
    }
}
