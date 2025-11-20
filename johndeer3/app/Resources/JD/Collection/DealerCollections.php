<?php

namespace App\Resources\JD\Collection;

use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * @OA\Schema(type="object", title="Dealer Collection",
 *     @OA\Property(property="data", type="array", @OA\Items(
 *          ref="#/components/schemas/DealerResource"
 *     )),
 *     @OA\Property(property="meta", title="Meta", type="object", ref="#/components/schemas/Meta"),
 *     @OA\Property(property="link", title="Link", type="object", ref="#/components/schemas/Link")
 * )
 */

class DealerCollections extends ResourceCollection
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
