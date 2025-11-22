<?php

namespace App\Services\Parsers\Drivers\ShipCars\Parsers;

use App\Models\Orders\Order;
use App\Models\Orders\Payment;
use App\Services\Parsers\ValueParserAbstract;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class PaymentParser extends ValueParserAbstract
{
    private const PATTERN_TOTAL_AMOUNT = "/^Total\s+Carrier\s+Pay\s+[$](?<amount>.+)$/m";
    private const PATTERN_BROKER_AMOUNT = "/^Broker\s+to\s+Carrier\s+[$](?<amount>.+)$/m";
    private const PATTERN_CUSTOMER_AMOUNT = "/^Customer\s+to\s+Carrier\s+\(at\s+(?<location>[^\)]+)\)\s+[$](?<amount>[^ ]+)\s+with\s+(?<method>.+)$/m";
    private const PATTERN_BROKER_FEE_AMOUNT = "/^Carrier\s+to\s+Broker\s+[$](?<amount>.+)$/m";

    private const METHOD_ACH = 'ach';
    private const METHOD_USHIP = 'uship';
    private const METHOD_CASH = 'cash';
    private const METHOD_COMCHECK = 'company check';

    private const RELATED_PAYMENT_METHOD = [
        self::METHOD_ACH => Payment::METHOD_ACH,
        self::METHOD_USHIP => Payment::METHOD_USHIP,
        self::METHOD_CASH => Payment::METHOD_CASH,
        self::METHOD_COMCHECK => Payment::METHOD_COMCHECK
    ];

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

    private ?array $terms = null;
    private string $text;

    public function parse(string $text): ?Collection
    {
        $text = trim($this->replaceBefore($text));
        $this->setTerms($text);
        $this->text = trim($this->replacementIntend($text));

        return $this
            ->setTotalAmount()
            ->setBrokerPayment()
            ->setCustomerPayment()
            ->setBrokerFeePayment()
            ->getResult();
    }

    private function setTerms(string $text): void
    {
        $terms = explode(
            "\n",
            trim(preg_replace("/.+\d{2}\/\d{2}\/\d{4}\n{2,}(.+)/s", "$1", $text))
        );
        if (count($terms) !== 2) {
            return;
        }
        $this->result['terms'] = implode("\n", $terms);
        $this->terms = [
            'estimated' => $terms[0],
            'method' => $terms[1]
        ];
    }

    private function setTotalAmount(): self
    {
        preg_match(self::PATTERN_TOTAL_AMOUNT, $this->text, $match);
        if (empty($match['amount'])) {
            return $this;
        }
        $this->result['total_carrier_amount'] = (float)$match['amount'];
        return $this;
    }

    private function setBrokerPayment(): self
    {
        preg_match(self::PATTERN_BROKER_AMOUNT, $this->text, $match);

        if (empty($match['amount'])) {
            return $this;
        }
        $amount = (float)$match['amount'];
        if (empty($amount)) {
            return $this;
        }
        $this->result['broker_payment_amount'] = $amount;
        if ($this->terms === null) {
            return $this;
        }
        preg_match(
            "/(?<method>" . self::METHOD_COMCHECK . "|" . self::METHOD_ACH . ")/i",
            $this->terms['method'],
            $match
        );
        if (!empty($match['method'])) {
            $this->result['broker_payment_method_id'] = $this->result['broker_payment_method']['id'] =
                self::RELATED_PAYMENT_METHOD[Str::lower($match['method'])];
            $this->result['broker_payment_method']['title'] = Payment::BROKER_METHODS[$this->result['broker_payment_method_id']];
        }
        preg_match("/\(?(?<days>[0-9]+)\)? business day/", $this->terms['estimated'], $match);
        if (!empty($match['days'])) {
            $this->result['broker_payment_days'] = (int) $match['days'];
        }
        preg_match("/(?<location>". Order::LOCATION_DELIVERY ."|" . Order::LOCATION_PICKUP . ")/i", $this->terms['estimated'], $match);
        if (!empty($match['location'])) {
            $this->result['broker_payment_begins'] = Str::lower($match['location']);
        } else {
            $this->result['broker_payment_begins'] = Order::INVOICE_SENT;
        }

        return $this;
    }

    private function setCustomerPayment(): self
    {
        preg_match(self::PATTERN_CUSTOMER_AMOUNT, $this->text, $match);
        if (empty($match)) {
            return $this;
        }
        $this->result['customer_payment_amount'] = (float) $match['amount'];
        $this->result['customer_payment_location'] = Str::lower($match['location']);

        preg_match("/(?<method>" . self::METHOD_CASH . "|" . self::METHOD_USHIP . ")/i", $match['method'], $match);
        if (!empty($match['method'])) {
            $this->result['customer_payment_method_id'] = $this->result['customer_payment_method']['id'] =
                self::RELATED_PAYMENT_METHOD[Str::lower($match['method'])];
            $this->result['customer_payment_method']['title'] = Payment::CUSTOMER_METHODS[$this->result['customer_payment_method_id']];
        }

        return $this;
    }

    private function setBrokerFeePayment(): self
    {
        preg_match(self::PATTERN_BROKER_FEE_AMOUNT, $this->text, $match);
        if (empty($match['amount'])) {
            return $this;
        }
        $amount = (float)$match['amount'];
        if (empty($amount)) {
            return $this;
        }
        $this->result['broker_fee_amount'] = $amount;
        if ($this->terms === null) {
            return $this;
        }
        preg_match(
            "/(?<method>" . self::METHOD_COMCHECK . "|" . self::METHOD_ACH . ")/i",
            $this->terms['method'],
            $match
        );
        if (!empty($match['method'])) {
            $this->result['broker_fee_method_id'] = $this->result['broker_fee_method']['id'] =
                self::RELATED_PAYMENT_METHOD[Str::lower($match['method'])];
            $this->result['broker_fee_method']['title'] = Payment::CARRIER_METHODS[$this->result['broker_fee_method_id']];
        }
        preg_match("/\(?(?<days>[0-9]+)\)? business day/", $this->terms['estimated'], $match);
        if (!empty($match['days'])) {
            $this->result['broker_fee_days'] = (int) $match['days'];
        }
        preg_match("/(?<location>". Order::LOCATION_DELIVERY ."|" . Order::LOCATION_PICKUP . ")/i", $this->terms['estimated'], $match);
        if (!empty($match['location'])) {
            $this->result['broker_fee_begins'] = Str::lower($match['location']);
        } else {
            $this->result['broker_fee_begins'] = Order::INVOICE_SENT;
        }

        return $this;
    }

    private function getResult(): Collection
    {
        return collect($this->result);
    }
}
