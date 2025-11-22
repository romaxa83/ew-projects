<?php

namespace App\Services\Fueling\Entity;

use App\Enums\Format\DateTimeEnum;
use App\Models\Fueling\Fueling;
use App\Rules\Fueling\FuelingCardExist;
use App\Rules\Fueling\FuelingDriverExist;
use App\Rules\Fueling\FuelingTransactionDateExist;
use Illuminate\Support\Facades\Validator;

class FuelingValidStatusEfs extends AbstractFuelingValidStatus
{
    public function __construct(Fueling $fueling)
    {
        $this->validator = Validator::make($fueling->toArray(),
            [
                'card' => ['required', 'digits:5', 'int', new FuelingCardExist($fueling)],
                'transaction_date' => ['required', new FuelingTransactionDateExist()],
                'user' => ['required', 'string', new FuelingDriverExist($fueling)],
                'location' => ['required', 'string'],
                'state' => ['required', 'string', 'size:2'],
                'fees' => ['nullable', 'numeric'],
                'item' => ['required', 'string', 'regex:/^[a-zA-Z]+$/u'],
                'unit_price' => ['required', 'numeric'],
                'quantity' => ['required', 'numeric'],
                'amount' => ['required', 'numeric'],
            ],
            [
                'location.string' => __('validation.invalid_location'),
                'unit_price.numeric' => __('validation.invalid_fueling_only_decimals'),
                'fees.numeric' => __('validation.invalid_fueling_only_decimals'),
                'quantity.numeric' => __('validation.invalid_fueling_only_decimals'),
                'amount.numeric' => __('validation.invalid_fueling_only_decimals'),
                'item.regex' => __('validation.invalid_fueling_only_text'),
            ]
        ) ;
    }
}
