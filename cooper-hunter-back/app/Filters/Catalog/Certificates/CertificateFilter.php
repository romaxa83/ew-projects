<?php

namespace App\Filters\Catalog\Certificates;

use App\Traits\Filter\IdFilterTrait;
use App\Traits\Filter\SortFilterTrait;
use EloquentFilter\ModelFilter;

class CertificateFilter extends ModelFilter
{
    use SortFilterTrait;
    use IdFilterTrait;

    public function link(string $link): void
    {
        $link = strtolower($link);

        $this->whereRaw('LOWER(`link`) LIKE ?', ["%$link%"]);
    }

    public function number(string $number): void
    {
        $number = strtolower($number);

        $this->whereRaw('LOWER(`number`) LIKE ?', ["%$number%"]);
    }

    public function type($typeId): void
    {
        $this->where('certificate_type_id', $typeId);
    }
}



