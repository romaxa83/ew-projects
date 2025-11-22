<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class UsersExport implements FromArray, WithHeadings, WithColumnFormatting
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
                $item['last_name'],
                $item['first_name'],
                $item['second_name'],
                $item['email'],
                !empty($item['branch']['name']) ? $item['branch']['name'] : '',
                $item['phones'][0]['phone'],
                $item['phones'][1]['phone'] ?? '',
                $item['phones'][2]['phone'] ?? '',
                $item['inspections_count'],
            ];
        }
        return $result;
    }

    public function headings(): array
    {
        return trans(key: 'export.heading.users', locale: $this->language);
    }

    public function columnFormats(): array
    {
        return [
            'G' => NumberFormat::FORMAT_TEXT,
            'H' => NumberFormat::FORMAT_TEXT,
            'I' => NumberFormat::FORMAT_TEXT,
        ];
    }
}
