<?php

namespace App\Http\Requests\Orders;

use App\Models\Contacts\Contact;
use App\Services\TimezoneService;
use App\Traits\Requests\ContactTransformerTrait;
use App\Traits\ValidationRulesTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property int save_pickup_contact
 */
class PickupRequest extends FormRequest
{
    use ContactTransformerTrait;
    use ValidationRulesTrait;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'pickup_contact.full_name' => ['required', 'string', 'min:2', 'max:255'],
            'pickup_contact.address' => ['required', 'string', 'min:2', 'max:255'],
            'pickup_contact.city' => ['required', 'string', 'max:255'],
            'pickup_contact.state_id' => ['required', 'integer', 'exists:App\Models\Locations\State,id'],
            'pickup_contact.comment' => ['nullable', 'string'],
            'pickup_contact.zip' => ['required', 'string', 'max:255'],
            'pickup_contact.phone' => [
                'required_with:pickup_contact.phone_extension',
                'nullable',
                $this->USAPhone(),
                'max:255'
            ],
            'pickup_contact.phone_extension' => ['nullable', 'string', 'max:255'],
            'pickup_contact.phone_name' => ['nullable', 'string', 'max:255'],
            'pickup_contact.phones' => ['nullable', 'array'],
            'pickup_contact.phones.*.name' => ['nullable', 'string', 'max:255'],
            'pickup_contact.phones.*.number' => [
                'required_with:pickup_contact.phones.*.extension',
                'nullable',
                $this->USAPhone(),
                'max:191'
            ],
            'pickup_contact.phones.*.extension' => ['nullable', 'string', 'max:255'],
            'pickup_contact.phones.*.notes' => ['nullable', 'string', 'max:255'],
            'pickup_contact.email' => ['nullable', 'string', 'max:255'],
            'pickup_contact.fax' => ['nullable', 'string', 'max:255'],
            'pickup_contact.type_id' => ['required', 'integer', Rule::in(array_keys(Contact::CONTACT_TYPES))],
            'pickup_contact.timezone' => [
                //'required_with:pickup_contact.working_hours',
                //'nullable',
                'required',
                Rule::in(resolve(TimezoneService::class)->getTimezonesArr()->pluck('timezone')->toArray())
            ],
            'pickup_contact.working_hours' => ['nullable', 'array'],
            'pickup_contact.working_hours.*.from' => ['required', 'date_format:g:i A'],
            'pickup_contact.working_hours.*.to' => ['required', 'date_format:g:i A'],
            'pickup_contact.working_hours.*.dayoff' => ['required', 'boolean'],

            'pickup_date' => ['nullable', 'date_format:m/d/Y'],
            'pickup_buyer_name_number' => ['nullable', 'string', 'max:255'],

            'pickup_time' => ['nullable', 'array'],
            'pickup_time.from' => ['nullable', 'date_format:g:i A'],
            'pickup_time.to' => ['nullable', 'date_format:g:i A'],

            'pickup_comment' => ['nullable', 'string'],

            'pickup_save_contact' => ['nullable', 'boolean'],
        ];
    }

    public function prepareForValidation()
    {
        $this->transform('pickup_contact');
    }
}
