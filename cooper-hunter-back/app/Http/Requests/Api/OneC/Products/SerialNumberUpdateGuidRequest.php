<?php

namespace App\Http\Requests\Api\OneC\Products;

use App\Http\Requests\Api\OneC\AbstractUpdateGuidRequest;
use App\Models\Catalog\Products\ProductSerialNumber;
use App\Permissions\Catalog\Products\UpdatePermission;

class SerialNumberUpdateGuidRequest extends AbstractUpdateGuidRequest
{
    protected const MODEL = ProductSerialNumber::class;
    protected const PRIMARY_KEY = 'id';

    public function authorize(): bool
    {
        return $this->user()->can(UpdatePermission::KEY);
    }
}
