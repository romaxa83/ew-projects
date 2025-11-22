<?php

namespace App\Http\Resources\Api\OneC\Catalog\Categories;

use App\Models\Catalog\Categories\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Category
 */
class CategoryResource extends JsonResource
{
    /**
     * @param Request $request
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'guid' => $this->guid,
            'active' => $this->active,
            'parent_id' => $this->parent?->id,
            'parent_guid' => $this->parent?->guid,
            'translations' => CategoryTranslationResource::collection($this->translations),
        ];
    }
}
