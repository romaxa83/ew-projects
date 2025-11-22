<?php

namespace App\Http\Resources\Api\OneC\Location;

use App\Models\Locations\Country;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Country
 */
class CountryResource extends JsonResource
{
    /**
     * @param Request $request
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->alias,
            'active' => $this->active,
            'default' => $this->default,
            'country_code' => $this->country_code,
            'translations' => CountryTranslationResource::collection($this->translations),
        ];
    }
}
