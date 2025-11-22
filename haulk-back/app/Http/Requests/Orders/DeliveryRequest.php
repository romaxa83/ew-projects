<?php

namespace App\Http\Requests\Orders;

use App\Models\Contacts\Contact;
use App\Services\TimezoneService;
use App\Traits\Requests\ContactTransformerTrait;
use App\Traits\ValidationRulesTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property int save_delivery_contact
 */
class DeliveryRequest extends FormRequest
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
            'delivery_contact.full_name' => ['required', 'string', 'min:2', 'max:255'],
            'delivery_contact.address' => ['required', 'string', 'min:2', 'max:255'],
            'delivery_contact.city' => ['required', 'string', 'max:255'],
            'delivery_contact.state_id' => ['required', 'integer', 'exists:App\Models\Locations\State,id'],
            'delivery_contact.comment' => ['nullable', 'string'],
            'delivery_contact.zip' => ['required', 'string', 'max:255'],
            'delivery_contact.phone' => [
                'required_with:delivery_phone_extension',
                'nullable',
                $this->USAPhone(),
                'max:255'
            ],
            'delivery_contact.phone_extension' => ['nullable', 'string', 'max:255'],
            'delivery_contact.phone_name' => ['nullable', 'string', 'max:255'],
            'delivery_contact.phones' => ['nullable', 'array'],
            'delivery_contact.phones.*.name' => ['nullable', 'string', 'max:255'],
            'delivery_contact.phones.*.number' => [
                'required_with:delivery_phones.*.extension',
                'nullable',
                $this->USAPhone(),
                'max:191'
            ],
            'delivery_contact.phones.*.extension' => ['nullable', 'string', 'max:255'],
            'delivery_contact.phones.*.notes' => ['nullable', 'string', 'max:255'],
            'delivery_contact.email' => ['nullable', 'string', 'max:255'],
            'delivery_contact.fax' => ['nullable', 'string', 'max:255'],
            'delivery_contact.type_id' => ['required', 'integer', Rule::in(array_keys(Contact::CONTACT_TYPES))],
            'delivery_contact.timezone' => [
                //'required_with:delivery_contact.working_hours',
                //'nullable',
                'required',
                Rule::in(resolve(TimezoneService::class)->getTimezonesArr()->pluck('timezone')->toArray())
            ],
            'delivery_contact.working_hours' => ['nullable', 'array'],
            'delivery_contact.working_hours.*.from' => ['required', 'date_format:g:i A'],
            'delivery_contact.working_hours.*.to' => ['required', 'date_format:g:i A'],
            'delivery_contact.working_hours.*.dayoff' => ['required', 'boolean'],

            'delivery_date' => ['nullable', 'date_format:m/d/Y'],
            'delivery_buyer_number' => ['nullable', 'string', 'max:255'],

            'delivery_time' => ['nullable', 'array'],
            'delivery_time.from' => ['nullable', 'date_format:g:i A'],
            'delivery_time.to' => ['nullable', 'date_format:g:i A'],

            'delivery_comment' => ['nullable', 'string'],

            'delivery_save_contact' => ['nullable', 'boolean'],
        ];
    }

    public function prepareForValidation()
    {
        $this->transform('delivery_contact');
    }
}
