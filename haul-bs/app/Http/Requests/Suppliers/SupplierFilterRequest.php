<?php

namespace App\Http\Requests\Suppliers;

use App\Foundations\Http\Requests\BaseFormRequest;

class SupplierFilterRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return array_merge(
            $this->paginationRule(),
            $this->searchRule(),
            $this->idRule(),
        );
    }
}
