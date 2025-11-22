<?php

namespace App\Http\Resources\Api\OneC\Catalog\Features;

use App\Models\Catalog\Features\FeatureTranslation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin FeatureTranslation
 */
class FeatureTranslationResource extends JsonResource
{
    /**
     * @param Request $request
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'language' => $this->language,
        ];
    }
}
