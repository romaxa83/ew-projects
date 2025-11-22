<?php


namespace App\Services\Parsers\Drivers\CentralDispatch\Parsers;


use App\Models\Orders\Order;
use App\Models\Orders\Payment;
use App\Services\Parsers\ValueParserAbstract;
use Illuminate\Support\Collection;

class PaymentParser extends ValueParserAbstract
{
    private const PAYMENT_COMPANY_CHECK = 'Company Check';
    private const PAYMENT_COD = 'Check on Delivery';
    private const PAYMENT_COP = 'Check on Pickup';
    private const PAYMENT_COMCHECK = 'Comchek';
    private const PAYMENT_CASH = 'Cash';
    private const PAYMENT_CERTIFIED_FUNDS = 'Certified Funds';
    private const PAYMENT_QUICKPAY = 'Quick Pay';

    private const PAYMENT_METHODS = [
        self::PAYMENT_COMPANY_CHECK,
        self::PAYMENT_COD,
        self::PAYMENT_COP,
        self::PAYMENT_COMCHECK,
        self::PAYMENT_CASH,
        self::PAYMENT_CERTIFIED_FUNDS,
        self::PAYMENT_QUICKPAY,
    ];

    private const PAYMENT_AS = [
        self::PAYMENT_COMPANY_CHECK => Payment::METHOD_CHECK,
        self::PAYMENT_COD => Payment::METHOD_CHECK,
        self::PAYMENT_COP => Payment::METHOD_CHECK,
        self::PAYMENT_COMCHECK => Payment::METHOD_CHECK,
        self::PAYMENT_CASH => Payment::METHOD_CASH,
        self::PAYMENT_CERTIFIED_FUNDS => Payment::METHOD_CERTIFIED_FUNDS,
        self::PAYMENT_QUICKPAY => Payment::METHOD_QUICKPAY,
    ];

    private const PAYMENT_BEGINS = [
        'pickup' => Order::LOCATION_PICKUP,
        'delivery' => Order::LOCATION_DELIVERY,
        'receiving a signed bill of lading' => Order::INVOICE_SENT
    ];

    private const PATTEN_INTEND = "/(?<intend>.+)Dispatch Info/i";
    private const PATTERN_CARRIER_AMOUNT = "/Total *Payment *to *Carrier\:\s*[\W](?<total_carrier_amount>[\d\,\.]+).*\n/is";
    private const PATTERN_CUSTOMER_AMOUNT = "/On *(Delivery|Pickup) *to *Carrier\:\s*[\W](?<customer_payment_amount>[\d\,\.]+).*\n/is";
    private const PATTERN_BROKER_AMOUNT = "/Company\* *owes *Carrier\:\s*[\W](?<broker_payment_amount>[\d\,\.]+).*\n/is";
    private const PATTERN_BROKER_FEE_AMOUNT = "/Carrier *owes *Company\*\*:\s*[\W](?<broker_fee_amount>[\d\,\.]*)/is";

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
            ->setCustomerPayment()
            ->setBrokerPayment()
            ->setBrokerFeePayment()
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

    private function setCustomerPayment(): PaymentParser
    {
        if (!preg_match(self::PATTERN_CUSTOMER_AMOUNT, $this->text, $match)) {
            return $this;
        }

        $this->result['customer_payment_amount'] = (float)preg_replace("/[^0-9\.]+/", "", $match['customer_payment_amount']);

        preg_match("/^\s*\*(?<method>C.+)$/im", $this->text, $match);

        if (empty($match['method'])) {
            return $this;
        }

        preg_match("/(?<location>" . Order::LOCATION_DELIVERY . "|" . Order::LOCATION_PICKUP . ")/i", $match['method'], $location);

        if (!empty($location['location'])) {
            $this->result['customer_payment_location'] = mb_convert_case($location['location'], MB_CASE_LOWER);
        }

        preg_match("/(?<method>" . implode("|" , self::PAYMENT_METHODS) . ")/i", $match['method'], $match);

        if (empty($match['method'])) {
            return $this;
        }

        $this->result['customer_payment_method_id'] = $this->result['customer_payment_method']['id'] = self::PAYMENT_AS[$match['method']];

        $this->result['customer_payment_method']['title'] = !empty($this->result['customer_payment_method_id']) ? Payment::CUSTOMER_METHODS[$this->result['customer_payment_method_id']] : null;

        return $this;
    }

    private function setBrokerPayment(): PaymentParser
    {
        if (!empty($this->result['customer_payment_amount'])) {
            return $this;
        }
        if (!preg_match(self::PATTERN_BROKER_AMOUNT, $this->text, $match)) {
            return $this;
        }

        $this->result['broker_payment_amount'] = (float)preg_replace("/[^0-9\.]+/", "", $match['broker_payment_amount']);

        $paymentData = $this->parseBrokerData();

        $this->result['broker_payment_method_id'] = $paymentData['method_id'];
        $this->result['broker_payment_method'] = $paymentData['method'];
        $this->result['broker_payment_days'] = $paymentData['payment_days'];
        $this->result['broker_payment_begins'] = $paymentData['payment_begins'];

        return $this;
    }

    private function setBrokerFeePayment(): PaymentParser
    {
        if (empty($this->result['customer_payment_amount']) || $this->result['customer_payment_amount'] <= $this->result['total_carrier_amount']) {
            return $this;
        }

        if (!preg_match(self::PATTERN_BROKER_FEE_AMOUNT, $this->text, $match)) {
            return $this;
        }

        $this->result['broker_fee_amount'] = (float)preg_replace("/[^0-9\.]+/", "", $match['broker_fee_amount']);

        $paymentData = $this->parseBrokerData();

        $this->result['broker_fee_method_id'] = $paymentData['method_id'];
        $this->result['broker_fee_method'] = $paymentData['method'];
        $this->result['broker_fee_days'] = $paymentData['payment_days'];
        $this->result['broker_fee_begins'] = $paymentData['payment_begins'];

        return $this;
    }

    private function parseBrokerData(): array
    {
        $this->setTerms();

        $result = [
            'method_id' => null,
            'method' => [
                'id' => null,
                'title' => null,
            ],
            'payment_days' => null,
            'payment_begins' => null
        ];

        preg_match("/[^\(]*(?<method>" . implode("|" , self::PAYMENT_METHODS) . ")[^\)]*/i", $this->result['terms'], $match);

        if (!empty($match['method'])) {
            $result['method_id'] = $result['method']['id'] = self::PAYMENT_AS[$match['method']];

            $result['method']['title'] = !empty($result['method_id']) ? Payment::BROKER_METHODS[$result['method_id']] : null;
        }

        preg_match("/(?<days>[0-9]+) +business days/is", $this->result['terms'], $match);

        if (!empty($match['days'])) {
            $result['payment_days'] = (int)$match['days'];
        } elseif (preg_match("/immediately/", $this->result['terms'])) {
            $result['payment_days'] = 0;
        }

        preg_match("/(?:on|of|upon) *(?<begins>" . implode("|", array_keys(self::PAYMENT_BEGINS)) . ")/is", $this->result['terms'], $match);

        if (!empty($match['begins'])) {
            $result['payment_begins'] = self::PAYMENT_BEGINS[mb_convert_case($match['begins'], MB_CASE_LOWER)];
        }

        return $result;
    }

    private function setTerms()
    {
        $text = preg_replace("/.*Condition[^\n]+\n/is", "", $this->text);
        $text = preg_replace("/ {4,}.+$/m", "", $text);
        $text = trim(preg_replace("/^[^a-z0-9]+$/im", "", $text));
        $text = trim(preg_replace("/\n\s*\*+[a-z].+/is", "", $text));
        $text = preg_replace("/\n/is", " ", $text);
        $text = preg_replace("/ {2,}/is", " ", $text);

        if (empty($text)) {
            return;
        }

        $this->result['terms'] = $text;
    }

    private function getCollection(): Collection
    {
        return collect($this->result);
    }
}
