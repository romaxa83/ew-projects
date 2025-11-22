<?php

namespace App\Services\Parsers\Drivers\CentralDispatch\Parsers;

use App\Services\Parsers\ValueParserAbstract;
use Illuminate\Support\Collection;

class PaymentParserV2 extends ValueParserAbstract
{
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
        $total = null;
        $terms = null;
        $text = trim($text);

        $firstPart = preg_replace('/.*(?=Load Info)/s', '', $text);
        $secondPart = preg_replace('/Vehicle Info.*$/s', '', $firstPart);

        $lines = explode("\n", $secondPart);

        $termsKey = null;
        foreach ($lines as $c => $line) {
            $line = str_replace("\f","", $line);
            if(strpos($line, "Total Price") !== false){
                $parts = preg_split('/\s{2,}/', $lines[$c+1]);
                $total = last($parts);
            }

            if(strpos($line, "Payment Terms") !== false){
                $termsKey = $c;
            }
        }

        foreach ($lines as $k => $line){
            if($k > $termsKey){
                if($line = trim($line)){
                    $terms .= $line . PHP_EOL;
                }
            }
        }

        $this->result['total_carrier_amount'] = $total;
        $this->result['terms'] = $terms;

        return collect($this->result);
    }
}

