<?php

namespace App\Rules\BodyShop\Inventory;

use App\Models\BodyShop\Inventories\Unit;
use Illuminate\Contracts\Validation\Rule;

class QuantityRule implements Rule
{
    private $unitId;

    /**
     * Create a new rule instance.
     * @param mixed $unitId
     * @return void
     */
    public function __construct($unitId)
    {
        $this->unitId = $unitId;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string $attribute
     * @param  mixed $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        if (!$this->unitId) {
            return false;
        }

        $unit = Unit::find($this->unitId);

        if (!$unit) {
            return false;
        }

        return $unit->accept_decimals || ($value - (int) $value) == 0;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return trans('The number must be integer value.');
    }
}
