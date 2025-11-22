<?php


namespace App\Services\Parsers\Drivers\TransportOrder\Parsers;


use App\Models\Orders\Payment;
use App\Services\Parsers\ValueParserAbstract;
use Illuminate\Support\Collection;

class PaymentParser extends ValueParserAbstract
{
    private const PAYMENT_QUICKPAY = 'QuickPay';

    private const PAYMENT_METHODS = [
        self::PAYMENT_QUICKPAY,
    ];

    private const PAYMENT_AS = [
        self::PAYMENT_QUICKPAY => Payment::METHOD_QUICKPAY,
    ];

    private const PATTERN_PAYMENT = "/PAYMENT TERMS: (?<method>.+?) {3,}TOTAL LOAD PRICE {3,}\\\$(?<price>[^ ]+)$/i";

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

        return $this->setPayment()
            ->getCollection();
    }

    private function setPayment(): PaymentParser
    {
        if (!preg_match(self::PATTERN_PAYMENT, $this->text, $match)) {
            return $this;
        }

        $this->result['total_carrier_amount'] = (float)$match['price'];
        $this->result['broker_payment_amount'] = (float)$match['price'];

        if (in_array($match['method'], self::PAYMENT_METHODS, true)) {
            $this->result['broker_payment_method_id'] =
                $this->result['broker_payment_method']['id'] = self::PAYMENT_AS[$match['method']];
            $this->result['broker_payment_method']['title'] = Payment::BROKER_METHODS[self::PAYMENT_AS[$match['method']]];
        }

        return $this;
    }

    private function getCollection(): Collection
    {
        return collect($this->result);
    }
}
