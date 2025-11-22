<?php

namespace App\Rules\Vehicles\Trucks;

use App\Models\Vehicles\Truck;
use App\Scopes\CompanyScope;
use Illuminate\Contracts\Validation\Rule;

class UniqueVinRule implements Rule
{
    private ?Truck $truck;

    public function __construct(?Truck $truck)
    {
        $this->truck = $truck;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  string|null  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        $trucks = Truck::query()
            ->withGlobalScope('company', new CompanyScope())
            ->where('vin', $value);


        if ($this->truck) {
            $trucks->where('id', '!=', $this->truck->id);
        }

        return !$trucks->exists();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return trans('validation.unique', ['attribute' => 'vin']);
    }
}
