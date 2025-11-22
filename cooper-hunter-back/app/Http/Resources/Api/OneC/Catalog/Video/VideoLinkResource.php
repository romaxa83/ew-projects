<?php

namespace App\Http\Resources\Api\OneC\Catalog\Video;

use App\Models\Catalog\Videos\VideoLink;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin VideoLink
 */
class VideoLinkResource extends JsonResource
{
    /**
     * @param Request $request
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'active' => $this->active,
            'link' => $this->link,
            'translation' => [
                'title' => $this->translation->title,
                'description' => $this->translation->description,
                'language' => $this->translation->language,
            ],
            'group' => [
                'id' => $this->group->id,
                'active' => $this->group->active,
                'translation' => [
                    'title' => $this->group->translation->title,
                    'description' => $this->group->translation->description,
                    'language' => $this->group->translation->language,
                ],
            ],
        ];
    }
}
