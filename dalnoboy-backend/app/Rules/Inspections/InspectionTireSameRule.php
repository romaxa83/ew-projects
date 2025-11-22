<?php

namespace App\Rules\Inspections;

use Illuminate\Contracts\Validation\Rule;

class InspectionTireSameRule implements Rule
{
    public function __construct(protected $data)
    {}

    public function passes($attribute, mixed $value): bool
    {
        $count = 0;
        foreach (data_get($this->data, 'inspection.tires', []) as $item) {
            if(data_get($item, 'tire_id') == $value){
                $count++;
            }
        }

        return $count == 1;
    }

    public function message(): string
    {
        return trans('inspections.validation_messages.tire.same_tire');
    }
}
