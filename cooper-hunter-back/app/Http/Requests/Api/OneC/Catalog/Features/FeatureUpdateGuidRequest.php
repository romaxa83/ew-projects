<?php

namespace App\Http\Requests\Api\OneC\Catalog\Features;

use App\Http\Requests\Api\OneC\AbstractUpdateGuidRequest;
use App\Models\Catalog\Features\Feature;
use App\Permissions\Catalog\Features\Features\UpdatePermission;

class FeatureUpdateGuidRequest extends AbstractUpdateGuidRequest
{
    protected const MODEL = Feature::class;

    public function authorize(): bool
    {
        return $this->user()->can(UpdatePermission::KEY);
    }
}
