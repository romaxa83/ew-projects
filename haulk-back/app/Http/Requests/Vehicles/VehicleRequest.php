<?php

namespace App\Http\Requests\Vehicles;

use App\Models\Tags\Tag;
use App\Models\Vehicles\Vehicle;
use App\Traits\Requests\OnlyValidateForm;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class VehicleRequest extends FormRequest
{
    use OnlyValidateForm;

    protected const MAX_ATTACHMENTS_COUNT = 5;

    public function rules(): array
    {
        return  [
            'vin' => ['required', 'string', 'max:191', 'alpha_num'],
            'unit_number' => ['required', 'string', 'min:2', 'max:10', 'alpha_num'],
            'year' => ['required', 'string', 'max:4'],
            'make' => ['required', 'string',],
            'model' => ['required', 'string'],
            'license_plate' => ['nullable', 'string', 'alpha_dash'],
            'notes' => ['nullable', 'string'],
            'tags' => ['nullable' ,'array', 'max:5'],
            'tags.*' => [
                'required',
                'int',
                Rule::exists(Tag::TABLE_NAME, 'id')
                    ->where('type', Tag::TYPE_TRUCKS_AND_TRAILER)
            ],
            Vehicle::ATTACHMENT_FIELD_NAME => ['nullable', 'array', 'max:' . $this->getMaxAttachmentsCount()],
            Vehicle::ATTACHMENT_FIELD_NAME . '.*' => ['file', $this->attachmentTypes(), 'max:10240'],
            'color' => ['nullable', 'string'],
            'gvwr' => ['nullable', 'numeric', 'min:1'],
        ];
    }

    public function attachmentTypes(): string
    {
        return 'mimes:pdf,png,jpg,jpeg';
    }

    abstract public function getVehicle(): ?Vehicle;

    public function getMaxAttachmentsCount(): int
    {
        $vehicle = $this->getVehicle();

        return $vehicle
            ? self::MAX_ATTACHMENTS_COUNT - count($vehicle->getAttachments())
            : self::MAX_ATTACHMENTS_COUNT;
    }
}
