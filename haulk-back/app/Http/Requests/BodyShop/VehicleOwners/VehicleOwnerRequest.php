<?php

namespace App\Http\Requests\BodyShop\VehicleOwners;

use App\Dto\VehicleOwners\VehicleOwnerDto;
use App\Models\BodyShop\VehicleOwners\VehicleOwner;
use App\Models\Tags\Tag;
use App\Traits\Requests\ContactTransformerTrait;
use App\Traits\Requests\OnlyValidateForm;
use App\Traits\ValidationRulesTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VehicleOwnerRequest extends FormRequest
{
    use ContactTransformerTrait;
    use OnlyValidateForm;
    use ValidationRulesTrait;

    public function rules(): array
    {
        /** @var VehicleOwner $vehicleOwner */
        $vehicleOwner = $this->route('vehicleOwner');

        $vehicleOwnerPhoneUniqueRule = Rule::unique(VehicleOwner::TABLE_NAME, 'phone');
        $vehicleOwnerEmailUniqueRule = Rule::unique(VehicleOwner::TABLE_NAME, 'email');
        if ($vehicleOwner) {
            $vehicleOwnerPhoneUniqueRule->ignore($vehicleOwner->id);
            $vehicleOwnerEmailUniqueRule->ignore($vehicleOwner->id);
        }

        return  [
            'first_name' => ['required', 'string', 'max:191', 'alpha'],
            'last_name' => ['required', 'string', 'max:191', 'alpha'],
            'phone' => ['required', 'string', $this->USAPhone(), 'max:191', $vehicleOwnerPhoneUniqueRule],
            'phone_extension' => ['nullable', 'string', 'max:191'],
            'phones' => ['array', 'nullable'],
            'phones.*.number' => ['nullable', $this->USAPhone(), 'string', 'max:191'],
            'phones.*.extension' => ['nullable', 'string', 'max:191'],
            'email' => ['required', 'email', $this->email(), 'max:191', $vehicleOwnerEmailUniqueRule],
            'notes' => ['nullable', 'string'],
            VehicleOwner::ATTACHMENT_FIELD_NAME => ['nullable', 'array'],
            VehicleOwner::ATTACHMENT_FIELD_NAME . '.*' => ['file', $this->orderAttachmentTypes()],
            'tags' => ['nullable' ,'array', 'max:5'],
            'tags.*' => [
                'required',
                'int',
                Rule::exists(Tag::TABLE_NAME, 'id')
                    ->where('type', Tag::TYPE_VEHICLE_OWNER)
            ],
        ];
    }

    protected function prepareForValidation()
    {
        $this->transformPhoneAttribute('phone');

        if ($this->has('email')) {
            $this->merge(
                [
                    'email' => mb_convert_case($this->input('email'), MB_CASE_LOWER),
                ]
            );
        }
    }

    public function getDto(): VehicleOwnerDto
    {
        return VehicleOwnerDto::byParams($this->validated());
    }
}
