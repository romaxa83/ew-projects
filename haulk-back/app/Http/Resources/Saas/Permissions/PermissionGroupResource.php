<?php

namespace App\Http\Resources\Saas\Permissions;

use App\Permissions\PermissionGroup;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read PermissionGroup $resource
 */
class PermissionGroupResource extends JsonResource
{
    public function toArray($request): array
    {
        return $this->resource->toArray();
    }
}

/**
 * @OA\Schema(schema="PermissionGroupResource", type="object", allOf={
 *     @OA\Schema(
 *           @OA\Property(property="key", type="string"),
 *           @OA\Property(property="name", type="string"),
 *           @OA\Property(property="position", type="integer"),
 *           @OA\Property(property="permissions", type="array",
 *                     @OA\Items(ref="#/components/schemas/Permission")
 *           ),
 *    )}
 * )
 */

/**
 * @OA\Schema(schema="Permission", type="object", allOf={
 *     @OA\Schema(
 *           @OA\Property(property="key", type="string"),
 *           @OA\Property(property="name", type="string"),
 *           @OA\Property(property="position", type="integer"),
 *    )}
 * )
 */
