<?php

namespace App\Validators\Orders;

use App\Models\Orders\Inspection;
use App\Models\Orders\Vehicle;
use Exception;
use Illuminate\Contracts\Validation\Rule;

abstract class ExteriorInspectionPhotoValidator implements Rule
{

    protected Vehicle $vehicle;

    protected int $photoLimit;

    public function __construct(Vehicle $vehicle)
    {
        $this->vehicle = $vehicle;

        $this->photoLimit = config('orders.inspection.max_photo');
    }

    /**
     * @param $attribute
     * @param $value
     * @return bool
     * @throws Exception
     */
    public function passes($attribute, $value): bool
    {
        if (is_null($inspection = $this->getInspection())) {
            throw new Exception('Order do not have an inspection!');
        }

        if ($media = $inspection->getPhoto($value)) {
            return true;
        }

        if ($inspection->getPhotos()->groupBy('collection_name')->count() >= $this->photoLimit) {
            return false;
        }

        return true;
    }

    abstract protected function getInspection(): Inspection;

    public function message(): string
    {
        return __('Photo inspection limit - :count', ['count' => $this->photoLimit]);
    }
}
