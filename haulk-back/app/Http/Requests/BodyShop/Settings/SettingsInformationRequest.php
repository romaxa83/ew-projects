<?php


namespace App\Http\Requests\BodyShop\Settings;

use App\Services\TimezoneService;
use App\Traits\Requests\OnlyValidateForm;
use App\Traits\ValidationRulesTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SettingsInformationRequest extends FormRequest
{
    use OnlyValidateForm;
    use ValidationRulesTrait;


    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'company_name' => ['required', 'string'],
            'address' => ['required', 'string', 'max:191'],
            'city' => ['required', 'string', 'max:255'],
            'state_id' => ['required', 'integer', 'exists:App\Models\Locations\State,id'],
            'zip' => ['required', 'string', 'min:3', 'max:10'],
            'timezone' => ['required', 'string', Rule::in(resolve(TimezoneService::class)->getTimezonesArr()->pluck('timezone')->toArray())],
            'phone' => ['required', 'string', $this->USAPhone(), 'max:191'],
            'phone_name' => ['nullable', 'string', 'max:255'],
            'phone_extension' => ['nullable', 'string', 'max:191'],
            'phones' => ['nullable', 'array', 'max:4'],
            'phones.*.name' => ['nullable', 'string', 'max:255'],
            'phones.*.number' => ['sometimes', 'string', $this->USAPhone(), 'max:191'],
            'phones.*.extension' => ['nullable', 'string', 'max:191'],
            'email' => ['required', 'string', 'email', $this->email(), 'max:255'],
            'fax' => ['nullable', 'string', 'max:191'],
            'website' => ['nullable', 'string', 'max:191'],
            'billing_phone' => ['required', $this->USAPhone(), 'string', 'max:191'],
            'billing_phone_name' => ['nullable', 'string', 'max:191'],
            'billing_phone_extension' => ['nullable', 'string', 'max:191'],
            'billing_phones' => ['nullable', 'array', 'max:4'],
            'billing_phones.*.name' => ['nullable', 'string', 'max:255'],
            'billing_phones.*.number' => ['required', 'string', $this->USAPhone(), 'max:191'],
            'billing_phones.*.extension' => ['nullable', 'string', 'max:191'],
            'billing_email' => ['nullable', 'email'],
            'billing_payment_details' => ['nullable', 'string'],
            'billing_terms' => ['nullable', 'string'],
        ];
    }
}
