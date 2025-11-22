<?php

namespace App\Http\Requests\BodyShop\Suppliers;

use App\Dto\BodyShop\Suppliers\SupplierDto;
use App\Rules\BodyShop\Suppliers\UniqueForMainContactRule;
use App\Traits\Requests\OnlyValidateForm;
use App\Traits\ValidationRulesTrait;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @property string type
 */
class SupplierRequest extends FormRequest
{
    use OnlyValidateForm;
    use ValidationRulesTrait;

    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'url' => ['nullable', 'string', 'url'],
            'contacts' => ['array', 'required', 'min:1'],
            'contacts.*.id' => ['nullable', 'int', 'exists:bs_supplier_contacts,id'],
            'contacts.*.is_main' => ['required', 'bool'],
            'contacts.*.name' => ['required', 'string'],
            'contacts.*.phone' => [
                'required',
                'string',
                $this->USAPhone(),
                'max:191',
                new UniqueForMainContactRule($this, 'phone'),
            ],
            'contacts.*.phone_extension' => ['nullable', 'string', 'max:191'],
            'contacts.*.phones' => ['array', 'nullable'],
            'contacts.*.phones.*.number' => ['required', $this->USAPhone(), 'string', 'max:191'],
            'contacts.*.phones.*.extension' => ['nullable', 'string', 'max:191'],
            'contacts.*.email' => [
                'required',
                'email',
                $this->email(),
                'max:191',
                new UniqueForMainContactRule($this, 'email'),
            ],
            'contacts.*.emails' => ['nullable', 'array'],
            'contacts.*.emails.*.value' => ['required', 'email', $this->email(), 'max:191'],
        ];
    }

    public function dto(): SupplierDto
    {
        return SupplierDto::byParams($this->validated());
    }
}
