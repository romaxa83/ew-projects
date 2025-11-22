<?php

namespace App\Http\Resources\Api\OneC\Orders\OrderCategories;

use App\Models\Orders\Categories\OrderCategoryTranslation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin OrderCategoryTranslation
 */
class OrderCategoryTranslationResource extends JsonResource
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
