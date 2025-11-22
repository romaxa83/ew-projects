<?php

namespace App\Http\Resources\Api\OneC\Catalog\TroubleshootingGroups;

use App\Models\Catalog\Troubleshoots\Group;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Group
 */
class TroubleshootingGroupResource extends JsonResource
{
    /**
     * @param Request $request
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'translation' => [
                'title' => $this->translation->title,
                'description' => $this->translation->description,
                'language' => $this->translation->language,
            ],
        ];
    }
}
