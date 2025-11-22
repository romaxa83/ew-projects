<?php

namespace App\Services\Fueling\Entity;

use Illuminate\Validation\Validator;

abstract class AbstractFuelingValidStatus implements FuelingValidStatus
{
    protected Validator $validator;

    public function messages(): array
    {
        return $this->validator->messages()->toArray();
    }

    public function passes(): bool
    {
        return $this->validator->passes();
    }
}
