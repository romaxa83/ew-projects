<?php

namespace App\Exports\CashRegistry;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class OperationExport implements FromArray, WithHeadings, WithMapping
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function headings(): array
    {
        return [
            'Date/Time',
            'Name',
            'Operation type',
            'Foreman',
            'Sum, $',
        ];
    }

    public function map($data): array
    {
        return [
            $data['insert_at']->format('m-d-Y H:i:s') ?? null,
            $data['executor']['name'] ?? null,
            $data['type']->label() ?? null,
            $data['foreman']['name'] ?? null,
            $data['sum'] ?? null,
        ];
    }

    public function array(): array
    {
        return $this->data;
    }
}

