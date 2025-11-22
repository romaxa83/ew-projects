<?php

namespace App\Http\Requests\Suppliers;

use App\Dto\Suppliers\SupplierDto;
use App\Foundations\Http\Requests\BaseFormRequest;
use App\Foundations\Rules\PhoneRule;
use App\Foundations\Traits\Requests\OnlyValidateForm;
use App\Models\Suppliers\SupplierContact;
use App\Rules\Suppliers\UniqueForMainContactRule;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(type="object", title="SupplierRequest",
 *     required={"name", "contacts"},
 *     @OA\Property(property="name", type="string", example="Golf Mill Ford"),
 *     @OA\Property(property="url", type="string", example="https://google.com", nullable=true),
 *     @OA\Property(property="contacts", type="array", @OA\Items(ref="#/components/schemas/SupplierContactRaw")),
 * )
 */
class SupplierRequest extends BaseFormRequest
{
    use OnlyValidateForm;

    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'url' => ['nullable', 'string', 'url'],
            'contacts' => ['array', 'required', 'min:1'],
            'contacts.*.id' => ['nullable', 'int', Rule::exists(SupplierContact::TABLE, 'id')],
            'contacts.*.is_main' => ['required', 'bool'],
            'contacts.*.name' => ['required', 'string'],
            'contacts.*.position' => ['nullable', 'string'],
            'contacts.*.phone' => [
                'required', 'string', new PhoneRule(), 'max:191',
                new UniqueForMainContactRule($this, 'phone'),
            ],
            'contacts.*.phone_extension' => ['nullable', 'string', 'max:191'],
            'contacts.*.phones' => ['array', 'nullable'],
            'contacts.*.phones.*.number' => ['required', new PhoneRule(), 'string', 'max:191'],
            'contacts.*.phones.*.extension' => ['nullable', 'string', 'max:191'],
            'contacts.*.email' => ['required', 'email', 'max:191',
                new UniqueForMainContactRule($this, 'email'),
            ],
            'contacts.*.emails' => ['nullable', 'array'],
            'contacts.*.emails.*.value' => ['required', 'email', 'max:191'],
        ];
    }

    public function getDto(): SupplierDto
    {
        return SupplierDto::byArgs($this->validated());
    }
}

