<?php

namespace App\Http\Resources\Api\OneC\Location;

use App\Models\Locations\State;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin State
 */
class StateResource extends JsonResource
{
    /**
     * @param Request $request
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'short_name' => $this->short_name,
            'status' => $this->status,
            'hvac_license' => $this->hvac_license,
            'epa_license' => $this->epa_license,
            'translations' => StateTranslationResource::collection($this->translations),
        ];
    }
}

