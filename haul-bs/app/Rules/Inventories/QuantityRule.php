<?php

namespace App\Rules\Inventories;

use App\Models\Inventories\Unit;
use Illuminate\Contracts\Validation\Rule;

class QuantityRule implements Rule
{
    private $unitId;
    private $attr;

    public function __construct($unitId)
    {
        $this->unitId = $unitId;
    }

    public function passes($attribute, $value): bool
    {
        $this->attr = $attribute;
        if (!$this->unitId) {
            return false;
        }

        $unit = Unit::find($this->unitId);

        if (!$unit) {
            return false;
        }

        return $unit->accept_decimals || ($value - (int)$value) == 0;
    }

    public function message(): string
    {
        return __("validation.integer", ["attribute" => $this->attr]);
    }
}

