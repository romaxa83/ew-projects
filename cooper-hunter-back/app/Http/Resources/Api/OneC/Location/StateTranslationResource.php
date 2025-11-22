<?php

namespace App\Http\Resources\Api\OneC\Location;

use App\Models\Locations\StateTranslation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin StateTranslation
 */
class StateTranslationResource extends JsonResource
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

