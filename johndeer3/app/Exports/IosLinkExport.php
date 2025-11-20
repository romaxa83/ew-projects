<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;

class IosLinkExport implements FromArray
{
    protected $links;

    public function __construct(array $links)
    {
        $this->links = $links;
    }

    public function array(): array
    {
        return $this->links;
    }
}
