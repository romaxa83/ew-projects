<?php

namespace App\Http\Resources\Api\OneC\Catalog\Categories;

use App\Models\Catalog\Categories\CategoryTranslation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin CategoryTranslation
 */
class CategoryTranslationResource extends JsonResource
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
            'seo_title' => $this->seo_title,
            'seo_description' => $this->seo_description,
            'seo_h1' => $this->seo_h1
        ];
    }
}
