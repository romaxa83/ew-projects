<?php

namespace App\Http\Requests\Fueling;

use App\Enums\Format\DateTimeEnum;
use App\Models\Fueling\FuelCard;
use App\Models\Users\User;
use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FuelingUpdateRequest extends FormRequest
{
    use OnlyValidateForm;

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'fuel_card_id' => ['required', 'integer', Rule::exists(FuelCard::TABLE_NAME, 'id')],
            'transaction_date' => ['required', 'date_format:' . DateTimeEnum::DATE_TIME_BACK],
            'timezone' => ['required', 'string'],

            'user_id' => ['required', 'integer', Rule::exists(User::TABLE_NAME, 'id')],
            'location' => ['required', 'string', 'max:25'],
            'state' => ['required', 'string', 'size:2'],
            'fees' => ['required', 'numeric'],
            'item' => ['required', 'string', 'max:10'],
            'unit_price' => ['required', 'numeric'],
            'quantity' => ['required', 'numeric'],
        ];
    }
}
