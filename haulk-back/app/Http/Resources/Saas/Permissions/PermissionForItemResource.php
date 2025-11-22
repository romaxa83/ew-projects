<?php

namespace App\Http\Resources\Saas\Permissions;

use Illuminate\Http\Resources\Json\JsonResource;

class PermissionForItemResource extends JsonResource
{
    public function toArray($request): array
    {
        $user = $request->user();

        $result = [];

        foreach ($this->resource ?? [] as $permission => $key) {
            if ($user && $user->can($permission)) {
                $result[$key] = $key;
            }
        }

        return array_values($result);
    }
}

/**
 * @OA\Schema(schema="PermissionForItemRaw", type="string", example="show|update|delete")
 */
