<?php

namespace App\Http\Resources\Api\OneC\Location;

use App\Models\Catalog\Categories\CategoryTranslation;
use App\Models\Locations\CountryTranslation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin CountryTranslation
 */
class CountryTranslationResource extends JsonResource
{
    /**
     * @param Request $request
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'language' => $this->language,
        ];
    }
}

