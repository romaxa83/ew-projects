<?php

namespace App\Http\Requests\Billing;

use App\Dto\Payments\PaymentMethodRequestDto;
use App\Models\Locations\State;
use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Foundation\Http\FormRequest;

class PaymentMethodRequest extends FormRequest
{
    use OnlyValidateForm;

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
            'first_name' => ['required', 'regex:/^[a-zA-Z]+$/', 'max:50'],
            'last_name' => ['required', 'regex:/^[a-zA-Z]+$/', 'max:50'],
            'address' => ['required', 'string', 'max:60'],
            'city' => ['required', 'string', 'max:40'],
            'state_id' => ['required', 'integer', 'exists:App\Models\Locations\State,id'],
            'zip' => ['required', 'string', 'min:3', 'max:10'],
            'card_number' => ['required', 'string', 'min:13', 'max:16'],
            'expires_at' => ['required', 'string', 'date_format:m/y'],
            'cvc' => ['required', 'string', 'min:3', 'max:4'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'first_name.regex' => trans('The first name must contain Latin.'),
            'last_name.regex' => trans('The last name must contain Latin.'),
        ];
    }

    public function getDto(): PaymentMethodRequestDto
    {
        $data = $this->validated();
        $state = State::find($data['state_id']);

        return new PaymentMethodRequestDto(
            $this->user()->id,
            $this->user()->email,
            $data['first_name'],
            $data['last_name'],
            $data['address'],
            $data['city'],
            $state->state_short_name,
            $data['zip'],
            $state->country_name,
            $data['card_number'],
            explode('/', $data['expires_at'])[0],
            substr(date('Y'), 0, 2) . explode('/', $data['expires_at'])[1],
            $data['cvc']
        );
    }
}
