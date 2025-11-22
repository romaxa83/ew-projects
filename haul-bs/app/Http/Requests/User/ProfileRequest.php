<?php

namespace App\Http\Requests\User;

use App\Dto\Users\ProfileDto;
use App\Foundations\Http\Requests\BaseFormRequest;
use App\Foundations\Rules\PhoneRule;
use App\Foundations\Traits\Requests\OnlyValidateForm;

/**
 * @OA\Schema(type="object", title="ProfileRequest",
 *     required={"first_name", "last_name"},
 *     @OA\Property(property="first_name", type="string", example="John"),
 *     @OA\Property(property="last_name", type="string", example="Doe"),
 *     @OA\Property(property="phone", type="string", example="1555999999", nullable=true),
 *     @OA\Property(property="phone_extension", type="string", example="9999", nullable=true),
 *     @OA\Property(property="phones", type="array", @OA\Items(ref="#/components/schemas/PhonesRaw"), nullable=true),
 * )
 */

class ProfileRequest extends BaseFormRequest
{
    use OnlyValidateForm;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'phone' => ['nullable', 'string', new PhoneRule(), 'max:191'],
            'phone_extension' => ['nullable', 'string', 'max:191'],
            'phones' => ['array', 'nullable'],
            'phones.*.number' => ['nullable', 'string', new PhoneRule(), 'max:191'],
            'phones.*.extension' => ['nullable', 'string', 'max:191'],
            'first_name' => ['required', 'string', 'max:191', 'alpha_spaces'],
            'last_name' => ['required', 'string', 'max:191', 'alpha_spaces'],
        ];
    }

    protected function prepareForValidation()
    {}

    public function getDto(): ProfileDto
    {
        return ProfileDto::byArgs($this->validated());
    }
}
