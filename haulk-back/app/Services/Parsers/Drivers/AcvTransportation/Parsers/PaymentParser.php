<?php


namespace App\Services\Parsers\Drivers\AcvTransportation\Parsers;


use App\Services\Parsers\ValueParserAbstract;
use Illuminate\Support\Collection;

class PaymentParser extends ValueParserAbstract
{

    private const PATTERN = "/Payout: *[^a-z0-9](?<total_carrier_amount>[0-9]+(?:\.[0-9]+)?)/s";

    public function parse(string $text): ?Collection
    {
        $text = trim($this->replaceBefore($text));

        if (!preg_match(self::PATTERN, $text, $match)) {
            return null;
        }

        return collect([
            'total_carrier_amount' => (float)$match['total_carrier_amount']
        ]);
    }
}
