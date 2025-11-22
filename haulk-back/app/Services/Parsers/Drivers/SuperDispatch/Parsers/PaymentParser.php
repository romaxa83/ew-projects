<?php

namespace App\Services\Parsers\Drivers\SuperDispatch\Parsers;

use App\Models\Orders\Order;
use App\Models\Orders\Payment;
use App\Services\Parsers\ValueParserAbstract;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class PaymentParser extends ValueParserAbstract
{
    private const PATTERN_TOTAL_AMOUNT = "/^Total Payment to Carrier: [$](?<amount>.+)$/m";
    private const PATTERN_BROKER_AMOUNT = "/^Shipper owes Carrier: [$](?<amount>.+)$/m";
    private const PATTERN_BROKER_FEE_AMOUNT = "/^Carrier owes Shipper: [$](?<amount>.+)$/m";
    private const PATTERN_TERMS = "/^Payment terms: (?<terms>.+)$/m";

    private const METHOD_COD_1 = 'cod';
    private const METHOD_COP_1 = 'cop';
    private const METHOD_COD_2 = 'check on delivery';
    private const METHOD_COP_2 = 'check on pickup';
    private const METHOD_CASH = 'cash';
    private const METHOD_ACH = 'ach';
    private const METHOD_QUICK_PAY_1 = 'quickpay';
    private const METHOD_QUICK_PAY_2 = 'quick pay';

    private const RELATED_PAYMENT_METHOD = [
        self::METHOD_COD_1 => Payment::METHOD_CHECK,
        self::METHOD_COD_2 => Payment::METHOD_CHECK,
        self::METHOD_COP_1 => Payment::METHOD_CHECK,
        self::METHOD_COP_2 => Payment::METHOD_CHECK,
        self::METHOD_CASH => Payment::METHOD_CASH,
        self::METHOD_ACH => Payment::METHOD_ACH,
        self::METHOD_QUICK_PAY_1 => Payment::METHOD_QUICKPAY,
        self::METHOD_QUICK_PAY_2 => Payment::METHOD_QUICKPAY,
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

    private string $terms;
    private float $brokerFeePaymentAmount = 0;
    private float $brokerPaymentAmount = 0;

    public function parse(string $text): ?Collection
    {
        $text = trim($this->replacementIntend($this->replaceBefore($text)));

        if (!$this->parseInfo($text)) {
            return null;
        }
        return $this->setCustomerPayment()
            ->setBrokerPayment()
            ->setBrokerFeePayment()
            ->getResult();
    }

    private function parseInfo(string $text): bool
    {
        preg_match(self::PATTERN_TOTAL_AMOUNT, $text, $match);
        if (empty($match['amount'])) {
            return false;
        }
        $this->result['total_carrier_amount'] = (float) trim($match['amount'], " \t\n\r\0\x0B*");
        preg_match(self::PATTERN_BROKER_AMOUNT, $text, $match);
        if (!empty($match['amount'])) {
            $this->brokerPaymentAmount = (float) trim($match['amount'], " \t\n\r\0\x0B*");
        }
        preg_match(self::PATTERN_BROKER_FEE_AMOUNT, $text, $match);
        if (!empty($match['amount'])) {
            $this->brokerFeePaymentAmount = (float) trim($match['amount'], " \t\n\r\0\x0B*");
        }
        preg_match(self::PATTERN_TERMS, $text, $match);
        if (!empty($match['terms'])) {
            $this->terms = trim($match['terms'], " \t\n\r\0\x0B*");
        }
        return true;
    }

    private function setCustomerPayment(): self
    {
        if (preg_match("/^(?<method>" . self::METHOD_COD_1 . "|" . self::METHOD_COP_1. ")$/", $this->terms, $match)) {
            $this->result['customer_payment_amount'] = $this->result['total_carrier_amount'] - $this->brokerPaymentAmount;
            $this->result['customer_payment_location'] = Str::lower($match['method']) === self::METHOD_COP_1 ? Order::LOCATION_PICKUP : Order::LOCATION_DELIVERY;
        } elseif (preg_match("/(?<location>" . Order::LOCATION_PICKUP . "|" . Order::LOCATION_DELIVERY . ")/i", $this->terms, $match)) {
            $this->result['customer_payment_amount'] = $this->result['total_carrier_amount'] - $this->brokerPaymentAmount;
            $this->result['customer_payment_location'] = Str::lower($match['location']);
        } else {
            return $this;
        }

        preg_match("/(?<method>" . self::METHOD_COP_1 . "|" . self::METHOD_COP_2 . "|" . self::METHOD_COD_1  . "|" . self::METHOD_COD_2  . "|" . self::METHOD_CASH . ")/i", $this->terms, $match);
        if (empty($match['method'])) {
            return $this;
        }

        $method = self::RELATED_PAYMENT_METHOD[Str::lower($match['method'])];

        $this->result['customer_payment_method_id'] = $this->result['customer_payment_method']['id'] = $method;
        $this->result['customer_payment_method']['title'] = Payment::CUSTOMER_METHODS[$this->result['customer_payment_method_id']];

        return $this;
    }

    private function setBrokerPayment(): self
    {
        if (empty($this->brokerPaymentAmount)) {
            return $this;
        }
        $this->result['broker_payment_amount'] = $this->brokerPaymentAmount;
        preg_match("/(?<method>" . self::METHOD_ACH. "|" . self::METHOD_QUICK_PAY_1 . "|" . self::METHOD_QUICK_PAY_2 . ")/i", $this->terms, $match);
        if (!empty($match['method'])) {
            $this->result['broker_payment_method_id'] = $this->result['broker_payment_method']['id'] = self::RELATED_PAYMENT_METHOD[Str::lower($match['method'])];
            $this->result['broker_payment_method']['title'] = Payment::BROKER_METHODS[$this->result['broker_payment_method_id']];
            return $this;
        }
        if (!preg_match("/^[0-9]+/", $this->terms)) {
            return $this;
        }
        $days = (int) preg_replace("/^([0-9]+).+/", "$1", $this->terms);
        $this->result['broker_payment_days'] = $days;

        return $this;
    }

    private function setBrokerFeePayment(): self
    {
        if (empty($this->brokerFeePaymentAmount)) {
            return $this;
        }
        $this->result['broker_fee_amount'] = $this->brokerFeePaymentAmount;

        return $this;
    }

    private function getResult(): Collection
    {
        return collect($this->result);
    }
}
