<?php

namespace App\Http\Requests\Orders;

use App\Dto\Orders\InspectExteriorDto;
use App\Models\Orders\Order;
use App\Models\Orders\Vehicle;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

/**
 * @property int photo_id
 * @property float photo_lat
 * @property float photo_lng
 * @property int photo_timestamp
 */
abstract class AbstractInspectExteriorRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var Vehicle $vehicle */
        $vehicle = $this->route('vehicle');

        return [
            'photo_id' => [
                'required',
                'integer',
                $this->getExteriorInspectionPhotoValidator($vehicle),
            ],
            Order::INSPECTION_PHOTO_FIELD_NAME => [
                'required',
                'file',
                'mimes:jpeg,jpg,png',
            ],
            'photo_lat' => ['required', 'numeric'],
            'photo_lng' => ['required', 'numeric'],
            'photo_timestamp' => ['nullable', 'integer'],
        ];
    }

    abstract protected function getExteriorInspectionPhotoValidator(Vehicle $vehicle): Rule;

    public function getDto(): InspectExteriorDto
    {
        return InspectExteriorDto::byParams(
            $this->photo_id,
            $this->photo_lat,
            $this->photo_lng,
            $this->file(Order::INSPECTION_PHOTO_FIELD_NAME),
            $this->photo_timestamp
                ? Carbon::createFromTimestamp($this->photo_timestamp, $this->header('TimezoneId'))
                : now($this->header('TimezoneId'))
        );
    }
}
