<?php

namespace App\Services\Fueling\Entity;

use App\Enums\Format\DateTimeEnum;
use App\Models\Fueling\Fueling;
use App\Rules\Fueling\FuelingCardExist;
use App\Rules\Fueling\FuelingDriverExist;
use Illuminate\Support\Facades\Validator;

class FuelingValidStatusQuikq extends AbstractFuelingValidStatus
{
    public function __construct(Fueling $fueling)
    {
        $this->validator = Validator::make($fueling->toArray(),
            [
                'card' => ['required', 'size:5', 'int', new FuelingCardExist($fueling)],
                'transaction_date' => ['required', 'date_format:' . DateTimeEnum::DATE_TIME_FRONT],
                'user' => ['required', 'string', new FuelingDriverExist($fueling)],
                'location' => ['required', 'string'],
                'state' => ['required', 'string', 'size:2'],
                'fees' => ['required', 'numeric'],
                'item' => ['required', 'string'],
                'unit_price' => ['required', 'numeric'],
                'quantity' => ['required', 'numeric'],
                'amount' => ['required', 'numeric'],
            ]
        ) ;
    }
}
