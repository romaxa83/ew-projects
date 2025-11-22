<?php

namespace App\Http\Resources\Api\OneC\Catalog\Manuals;

use App\Models\Catalog\Manuals\Manual;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Manual
 */
class ManualResource extends JsonResource
{
    /**
     * @param Request $request
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'group' => [
                'id' => $this->group->id,
                'active' => $this->group->active,
                'translation' => [
                    'title' => $this->group->translation->title,
                    'language' => $this->group->translation->language,
                ],
            ],
        ];
    }
}
