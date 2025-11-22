<?php

namespace App\Services\Parsers\Drivers\ReadyLogistics\Parsers;

use App\Services\Parsers\ValueParserAbstract;
use Illuminate\Support\Collection;

class PaymentParser extends ValueParserAbstract
{

    private const PATTEN_INTEND = "/(?<intend>.+)Dispatch Info/i";
    private const PATTERN_CARRIER_AMOUNT = '/\$(?<total_carrier_amount>[0-9,\.]+)/';

    private string $text;
    private array $result = [
        'total_carrier_amount' => null,
        'terms' => null,
        'customer_payment_amount' => null,
        'customer_payment_method_id' => null,
        'customer_payment_method' => [
            'id' => null,
            'title' => null
        ],
        'customer_payment_location' => null,
        'broker_payment_amount' => null,
        'broker_payment_method_id' => null,
        'broker_payment_method' => [
            'id' => null,
            'title' => null
        ],
        'broker_payment_days' => null,
        'broker_payment_begins' => null,
        'broker_fee_amount' => null,
        'broker_fee_method_id' => null,
        'broker_fee_method' => [
            'id' => null,
            'title' => null
        ],
        'broker_fee_days' => null,
        'broker_fee_begins' => null,
    ];

    public function parse(string $text): Collection
    {
        $this->text = trim($this->replaceBefore($text));

        return $this->clearText()
            ->setCarrierPayment()
            ->getCollection();
    }

    private function clearText(): PaymentParser
    {
        if (!preg_match(self::PATTEN_INTEND, $this->text, $match)) {
            return $this;
        }

        $intend = mb_strlen($match['intend']);

        $this->text = trim(preg_replace("/^(.{0," . $intend ."}).*/m", "$1", $this->text));
        return $this;
    }


    private function setCarrierPayment(): PaymentParser
    {
        if (!preg_match(self::PATTERN_CARRIER_AMOUNT, $this->text, $match)) {
            return $this;
        }
        $this->result['total_carrier_amount'] = (float)preg_replace("/[^0-9\.]+/", "", $match['total_carrier_amount']);

        return $this;
    }

    private function getCollection(): Collection
    {
        return collect($this->result);
    }
}
