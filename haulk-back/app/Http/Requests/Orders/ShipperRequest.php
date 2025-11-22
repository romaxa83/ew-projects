<?php

namespace App\Http\Requests\Orders;

use App\Models\Contacts\Contact;
use App\Services\TimezoneService;
use App\Traits\Requests\ContactTransformerTrait;
use App\Traits\ValidationRulesTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property int save_shipper_contact
 * @property int shipper_copy_delivery
 */
class ShipperRequest extends FormRequest
{
    use ContactTransformerTrait;
    use ValidationRulesTrait;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        if ($this->shipper_copy_delivery) {
            return [
                'shipper_copy_delivery' => ['nullable', 'boolean'],
            ];
        }

        return [
            'shipper_contact.full_name' => ['required', 'string', 'min:2', 'max:255'],
            'shipper_contact.address' => ['required', 'string', 'min:2', 'max:255'],
            'shipper_contact.city' => ['required', 'string', 'max:255'],
            'shipper_contact.state_id' => ['required', 'integer', 'exists:App\Models\Locations\State,id'],
            'shipper_contact.zip' => ['required', 'string', 'max:255'],
            'shipper_contact.comment' => ['nullable', 'string'],
            'shipper_contact.phone' => [
                'required_with:shipper_contact.phone_extension',
                'nullable',
                $this->USAPhone(),
                'max:255'
            ],
            'shipper_contact.phone_extension' => ['nullable', 'string', 'max:255'],
            'shipper_contact.phone_name' => ['nullable', 'string', 'max:255'],
            'shipper_contact.phones' => ['nullable', 'array'],
            'shipper_contact.phones.*.name' => ['nullable', 'string', 'max:255'],
            'shipper_contact.phones.*.number' => [
                'required_with:shipper_contact.phones.*.extension',
                'nullable',
                $this->USAPhone(),
                'max:191'
            ],
            'shipper_contact.phones.*.extension' => ['nullable', 'string', 'max:255'],
            'shipper_contact.email' => ['nullable', 'string', 'max:255'],
            'shipper_contact.fax' => ['nullable', 'string', 'max:255'],
            'shipper_contact.type_id' => ['required', 'integer', Rule::in(array_keys(Contact::CONTACT_TYPES))],
            'shipper_contact.timezone' => [
                //'required_with:shipper_contact.working_hours',
                //'nullable',
                'required',
                Rule::in(resolve(TimezoneService::class)->getTimezonesArr()->pluck('timezone')->toArray())
            ],
            'shipper_contact.working_hours' => ['nullable', 'array'],
            'shipper_contact.working_hours.*.from' => ['required', 'date_format:g:i A'],
            'shipper_contact.working_hours.*.to' => ['required', 'date_format:g:i A'],
            'shipper_contact.working_hours.*.dayoff' => ['required', 'boolean'],

            'shipper_comment' => ['nullable', 'string'],

            'shipper_save_contact' => ['nullable', 'boolean'],
        ];
    }

    public function prepareForValidation()
    {
        $this->transform('shipper_contact');
    }
}
