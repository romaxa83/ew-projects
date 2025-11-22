<?php

namespace App\Http\Resources\Api\OneC\Orders\OrderCategories;

use App\Models\Orders\Categories\OrderCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin OrderCategory
 */
class OrderCategoryResource extends JsonResource
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
            'need_description' => $this->need_description,
            'translations' => OrderCategoryTranslationResource::collection($this->translations),
        ];
    }
}
