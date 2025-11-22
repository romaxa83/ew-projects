<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class BranchesExport implements FromArray, WithHeadings, WithColumnFormatting
{
    public function __construct(private array $data, private string $language)
    {
    }

    public function array(): array
    {
        $result = [];
        foreach ($this->data as $item) {
            $result[] = [
                $item['id'],
                $item['name'],
                $item['city'],
                $item['region']['translate']['title'],
                $item['address'],
                $item['phones'][0]['phone'],
                $item['phones'][1]['phone'] ?? '',
                $item['phones'][2]['phone'] ?? '',
                $item['inspections_count']
            ];
        }
        return $result;
    }

    public function headings(): array
    {
        return trans(key: 'export.heading.branches', locale: $this->language);
    }

    public function columnFormats(): array
    {
        return [
            'F' => NumberFormat::FORMAT_TEXT,
            'G' => NumberFormat::FORMAT_TEXT,
            'H' => NumberFormat::FORMAT_TEXT,
        ];
    }
}
