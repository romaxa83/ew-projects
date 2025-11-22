<?php

namespace App\Http\Requests\Inventories\Brand;

use App\Foundations\Http\Requests\Common\SearchRequest;

class BrandShortListRequest extends SearchRequest
{
    public function rules(): array
    {
        return parent::rules();
    }
}

