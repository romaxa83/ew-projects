<?php

namespace App\Http\Requests\Customers;

use App\Foundations\Http\Requests\BaseFormRequest;

class CustomerEComFilterRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return array_merge(
            $this->paginationRule(),
            $this->idRule(),
            [
                'email' => ['nullable', 'string', 'email'],
            ]
        );
    }
}
