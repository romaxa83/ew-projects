<?php


namespace App\Services\Parsers\Drivers\FisherShipping\Parsers;


use App\Services\Parsers\ValueParserAbstract;
use Illuminate\Support\Collection;

class PaymentParser extends ValueParserAbstract
{

    private const PATTERN_CHECK_IS_SET_PAYMENT = "/Payment Terms/s";

    private const PATTERN_PRICE = "/.+?[\$](?<total_carrier_amount>[0-9]+(?:[\.,][0-9]+)?)/s";
    private const PATTERN_BROKER_FEE = "/broker fee.+?[\$](?<broker_fee_amount>[0-9]+(?:[\.,][0-9]+)?)/is";

    public function parse(string $text): ?Collection
    {
        if (!$this->isSetPaymentData($text)) {
            return null;
        }

        $text = trim($this->replaceBefore($text));

        preg_match(self::PATTERN_PRICE, $text, $match);

        $price = $broker_fee = null;

        if (!empty($match['total_carrier_amount'])) {
            $price = (float)preg_replace("/,/", ".", $match['total_carrier_amount']);
        }

        preg_match(self::PATTERN_BROKER_FEE, $text, $match);

        if (!empty($match['broker_fee_amount'])) {
            $broker_fee = (float)preg_replace("/,/", ".", $match['broker_fee_amount']);
        }

        return collect([
            'total_carrier_amount' => $price,
            'broker_fee_amount' => $broker_fee
        ]);
    }

    private function isSetPaymentData(string $text): bool
    {
        return (bool)preg_match(self::PATTERN_CHECK_IS_SET_PAYMENT, $text);
    }
}
