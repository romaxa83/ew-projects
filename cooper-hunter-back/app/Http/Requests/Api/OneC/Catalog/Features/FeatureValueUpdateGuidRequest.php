<?php

namespace App\Http\Requests\Api\OneC\Catalog\Features;

use App\Http\Requests\Api\OneC\AbstractUpdateGuidRequest;
use App\Models\Catalog\Features\Value;
use App\Permissions\Catalog\Features\Values\UpdatePermission;

class FeatureValueUpdateGuidRequest extends AbstractUpdateGuidRequest
{
    protected const MODEL = Value::class;

    public function authorize(): bool
    {
        return $this->user()->can(UpdatePermission::KEY);
    }
}
