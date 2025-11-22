<?php

namespace App\Http\Requests\Customers;

use App\Foundations\Http\Requests\Common\SearchRequest;
use App\Foundations\Traits\Requests\OnlyValidateForm;

class CustomerShortListRequest extends SearchRequest
{
    use OnlyValidateForm;

    public function rules(): array
    {
        return parent::rules();
    }
}
