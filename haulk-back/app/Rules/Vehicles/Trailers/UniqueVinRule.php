<?php

namespace App\Rules\Vehicles\Trailers;

use App\Models\Vehicles\Trailer;
use App\Scopes\CompanyScope;
use Illuminate\Contracts\Validation\Rule;

class UniqueVinRule implements Rule
{
    private ?Trailer $trailer;

    public function __construct(?Trailer $trailer)
    {
        $this->trailer = $trailer;
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
        $trailers = Trailer::query()
            ->withGlobalScope('company', new CompanyScope())
            ->where('vin', $value);


        if ($this->trailer) {
            $trailers->where('id', '!=', $this->trailer->id);
        }

        return !$trailers->exists();
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
