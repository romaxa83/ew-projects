<?php

namespace App\Http\Requests\Api\OneC\Products;

use App\Http\Requests\BaseFormRequest;
use JetBrains\PhpStorm\Pure;

class SerialNumbersIndexRequest extends BaseFormRequest
{
    #[Pure] public function rules(): array
    {
        return $this->getPaginationRules();
    }
}
