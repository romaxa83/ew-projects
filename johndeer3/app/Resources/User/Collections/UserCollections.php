<?php

namespace App\Resources\User\Collections;

use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * @OA\Schema(type="object", title="User Collection",
 *     @OA\Property(property="data", type="array", @OA\Items(
 *          ref="#/components/schemas/UserResource"
 *     )),
 *     @OA\Property(property="meta", title="Meta", type="object", ref="#/components/schemas/Meta"),
 *     @OA\Property(property="link", title="Link", type="object", ref="#/components/schemas/Link")
 * )
 */

class UserCollections extends ResourceCollection
{
    public function toArray($request): array
    {
        return [
            'data' => $this->collection,
            'links' => [
                'self' => 'link-value',
            ],
        ];
    }
}
