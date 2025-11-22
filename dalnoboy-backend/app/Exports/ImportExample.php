<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;

class ImportExample implements FromArray
{
    public function __construct(private array $data, private string $language)
    {
    }

    public function array(): array
    {
        return $this->data;
    }
}
