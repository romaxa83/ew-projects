<?php

namespace App\Resources\Report\Collection;

use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * @OA\Schema(type="object", title="ReportList Collections",
 *     @OA\Property(property="data", type="array", @OA\Items(
 *          ref="#/components/schemas/ReportListResource"
 *     )),
 *     @OA\Property(property="meta", title="Meta", type="object", ref="#/components/schemas/Meta"),
 *     @OA\Property(property="link", title="Link", type="object", ref="#/components/schemas/Link")
 * )
 */

class ReportListCollections extends ResourceCollection
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
