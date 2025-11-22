<?php


namespace App\Services\Parsers\Drivers\DirectConnectLogistix\Parsers;

use App\Services\Parsers\ValueParserAbstract;
use Illuminate\Support\Collection;

class PaymentParser extends ValueParserAbstract
{

    private const PATTERN = "/Rate:\s*[^a-z0-9\s](?<total_carrier_amount>[0-9]+(?:(?:\.|,)[0-9]+)?)/m";

    public function parse(string $text): ?Collection
    {
        $text = trim($this->replaceBefore($text));

        if (!preg_match(self::PATTERN, $text, $match)) {
            return null;
        }

        return collect([
            'total_carrier_amount' => (float)preg_replace("/,/", ".", $match['total_carrier_amount'])
        ]);
    }
}
