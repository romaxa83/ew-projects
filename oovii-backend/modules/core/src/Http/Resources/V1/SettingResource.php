<?php

namespace WezomCms\Core\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     type="object",
 *     title="Setting resource",
 *     description="Setting resource",
 * )
 */
class SettingResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'key' => $this['key'],
            'value' => $this['value'],
        ];
    }

    /**
     *  @OA\Property(property="key", title="Key", description="Ключ параметра", example="users.social_links.twitter_link"),
     *  @OA\Property(property="value", title="Value", description="Значение параметра", example="https://twitter.com/")
     */
}

