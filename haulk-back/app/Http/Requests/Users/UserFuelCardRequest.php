<?php

namespace App\Http\Requests\Users;

use App\Models\Fueling\FuelCard;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserFuelCardRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'fuel_card_id' => ['required', 'integer', Rule::exists(FuelCard::TABLE_NAME, 'id')],
        ];
    }
}
