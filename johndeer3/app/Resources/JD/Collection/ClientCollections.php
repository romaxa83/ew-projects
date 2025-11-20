<?php

namespace App\Resources\JD\Collection;

use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * @OA\Schema(type="object", title="Client Collections",
 *     @OA\Property(property="data", type="array", @OA\Items(
 *          ref="#/components/schemas/ClientResource"
 *     )),
 *     @OA\Property(property="meta", title="Meta", type="object", ref="#/components/schemas/Meta"),
 *     @OA\Property(property="link", title="Link", type="object", ref="#/components/schemas/Link")
 * )
 */

class ClientCollections extends ResourceCollection
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
