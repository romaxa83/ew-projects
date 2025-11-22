<?php

namespace App\Http\Resources\Api\OneC\Products;

use App\Models\Catalog\Products\ProductTranslation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ProductTranslation
 */
class ProductTranslationResource extends JsonResource
{
    /**
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'language' => $this->language,
            'description' => $this->description,
            'seo_title' => $this->seo_title,
            'seo_description' => $this->seo_description,
            'seo_h1' => $this->seo_h1,
        ];
    }
}
