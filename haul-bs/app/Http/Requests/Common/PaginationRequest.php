<?php

namespace App\Http\Requests\Common;

use App\Foundations\Http\Requests\BaseFormRequest;

class PaginationRequest extends BaseFormRequest
{
    public function rules(): array
    {
        return $this->paginationRule();
    }
}
