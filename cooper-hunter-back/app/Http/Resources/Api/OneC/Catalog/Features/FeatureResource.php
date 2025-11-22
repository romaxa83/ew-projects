<?php

namespace App\Http\Resources\Api\OneC\Catalog\Features;

use App\Models\Catalog\Features\Feature;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Feature
 */
class FeatureResource extends JsonResource
{
    /**
     * @param Request $request
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'guid' => $this->guid,
            'active' => $this->active,
            'display_in_web' => $this->display_in_web,
            'display_in_mobile' => $this->display_in_mobile,
            'translations' => FeatureTranslationResource::collection($this->translations),
            'values' => ValueResource::collection($this->values)
        ];
    }
}
