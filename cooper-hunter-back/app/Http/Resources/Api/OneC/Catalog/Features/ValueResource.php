<?php

namespace App\Http\Resources\Api\OneC\Catalog\Features;

use App\Models\Catalog\Features\Value;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Value
 */
class ValueResource extends JsonResource
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
            'title' => $this->title,
        ];
    }
}
