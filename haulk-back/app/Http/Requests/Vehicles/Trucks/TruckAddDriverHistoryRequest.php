<?php

namespace App\Http\Requests\Vehicles\Trucks;

use App\Enums\Format\DateTimeEnum;
use App\Models\Users\User;
use App\Rules\Vehicles\DriverHistory\StartAtRule;
use App\Traits\Requests\OnlyValidateForm;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TruckAddDriverHistoryRequest extends FormRequest
{
    use OnlyValidateForm;


    public function rules(): array
    {
        return [
            'driver_id' => [
                'required',
               'integer',
                Rule::exists(User::TABLE_NAME, 'id'),
            ],
            'start_at' => [
                'required',
                'string',
                'date_format:'.DateTimeEnum::DATE_TIME_FRONT,
                new StartAtRule()
            ],
            'end_at' => [
                'required',
                'string',
                'date_format:'.DateTimeEnum::DATE_TIME_FRONT,
                'after:start_at',
                'before:' . $this->getDateByTimezone()
            ],
        ];
    }

    public function getDateByTimezone(): CarbonImmutable
    {
        $date = CarbonImmutable::now();

        if(isset(authUser()->getCompany()->timezone)){
            $date =CarbonImmutable::now(authUser()->getCompany()->timezone);
        }

        return $date;
    }
}
