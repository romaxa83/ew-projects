<?php

namespace App\Services\Parsers\Drivers\Rpm\Parsers;

use App\Services\Parsers\ValueParserAbstract;
use Illuminate\Support\Collection;

class PaymentParser extends ValueParserAbstract
{
    public function parse(string $text): ?Collection
    {
        $total = (float)trim($this->replaceBefore($text));
        return collect(
            [
                'total_carrier_amount' => !empty($total) ? $total : null
            ]
        );
    }
}
