<?php

namespace App\Http\Requests\Vehicles;

use App\Enums\Tags\TagType;
use App\Enums\Vehicles\VehicleType;
use App\Foundations\Http\Requests\BaseFormRequest;
use App\Foundations\Traits\Requests\OnlyValidateForm;
use App\Models\Customers\Customer;
use App\Models\Tags\Tag;
use App\Models\Vehicles\Vehicle;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(type="object", title="TruckRequest",
 *     required={"vin", "unit_number", "year", "make", "model", "type", "owner_id", "license_plate"},
 *     @OA\Property(property="vin", type="string", example="1FT8W3CT3LED10823"),
 *     @OA\Property(property="unit_number", type="string", example="SL56"),
 *     @OA\Property(property="year", type="string", example="2022"),
 *     @OA\Property(property="make", type="string", example="FORD"),
 *     @OA\Property(property="model", type="string", example="F-350"),
 *     @OA\Property(property="type", type="integer", example="8"),
 *     @OA\Property(property="owner_id", type="integer", example="2"),
 *     @OA\Property(property="license_plate", type="string", example="TK348OKT"),
 *     @OA\Property(property="notes", type="string", example="some text"),
 *     @OA\Property(property="color", type="string", example="black"),
 *     @OA\Property(property="gvwr", type="number", example="100.9"),
 *     @OA\Property(property="tags", type="array", description="Tag id list", example={1, 22, 3},
 *         @OA\Items(type="integer")
 *     ),
 *     @OA\Property(property="attachment_files", type="array",
 *          @OA\Items(type="file")
 *     ),
 * )
 */

class VehicleRequest extends BaseFormRequest
{
    use OnlyValidateForm;

    public function rules(): array
    {
        return  [
            'vin' => ['required', 'string', 'max:191', 'alpha_num'],
            'unit_number' => ['required', 'string', 'min:2', 'max:10', 'alpha_num'],
            'year' => ['required', 'string', 'max:4'],
            'make' => ['required', 'string',],
            'model' => ['required', 'string'],
            'type' => ['required', 'integer', VehicleType::ruleIn()],
            'owner_id' => ['required', 'int', Rule::exists(Customer::TABLE, 'id')],
            'notes' => ['nullable', 'string'],
            'license_plate' => ['required', 'string', 'alpha_dash'],
            'tags' => ['nullable' ,'array', 'max:5'],
            'tags.*' => ['required', 'int',
                Rule::exists(Tag::TABLE, 'id')
                    ->where('type', TagType::TRUCKS_AND_TRAILER)
            ],
            Vehicle::ATTACHMENT_FIELD_NAME => ['nullable', 'array', 'max:' . $this->getMaxAttachmentsCount()],
            Vehicle::ATTACHMENT_FIELD_NAME . '.*' => ['file', 'mimes:pdf,png,jpg,jpeg', 'max:10240'],
            'color' => ['nullable', 'string'],
            'gvwr' => ['nullable', 'numeric', 'min:1'],
        ];
    }

    public function getMaxAttachmentsCount(): int
    {
        $model = $this->getModel();

        return $model
            ? Vehicle::MAX_ATTACHMENTS_COUNT - count($model->getAttachments())
            : Vehicle::MAX_ATTACHMENTS_COUNT;
    }
}
