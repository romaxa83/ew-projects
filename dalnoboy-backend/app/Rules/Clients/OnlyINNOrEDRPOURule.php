<?php

namespace App\Rules\Clients;

use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\Rule;

class OnlyINNOrEDRPOURule implements Rule, DataAwareRule
{
    protected array $data = [];

    public function passes($attribute, $value): bool
    {
        return empty($this->data['edrpou']);
    }

    public function message(): string
    {
        return trans('validation.custom.clients.only_inn_or_edrpou');
    }

    public function setData(mixed $data): self
    {
        $this->data = $data['client'];

        return $this;
    }
}
