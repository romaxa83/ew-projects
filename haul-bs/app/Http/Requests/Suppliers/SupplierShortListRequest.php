<?php

namespace App\Http\Requests\Suppliers;

use App\Foundations\Http\Requests\Common\SearchRequest;

class SupplierShortListRequest extends SearchRequest
{
    public function rules(): array
    {
        return parent::rules();
    }
}
