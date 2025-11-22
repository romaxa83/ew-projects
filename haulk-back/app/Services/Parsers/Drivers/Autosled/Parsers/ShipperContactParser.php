<?php

namespace App\Services\Parsers\Drivers\Autosled\Parsers;

use App\Services\Parsers\ValueParserAbstract;
use Illuminate\Support\Collection;

class ShipperContactParser extends ValueParserAbstract
{
    public function parse(string $text): Collection
    {
        return collect(
            [
                'full_name' => $this->replaceBefore($text)
            ]
        );
    }
}
