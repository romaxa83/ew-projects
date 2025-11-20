<?php

namespace App\Http\Resources\Departments;

use App\Models\Departments\Department;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(type="object", title="DepartmentResource",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="guid", type="string", example="46d93bc9-2d53-4368-8cf6-25404bff61f5"),
 *     @OA\Property(property="name", type="string", example="Office Manager"),
 *     @OA\Property(property="number", type="integer", example="94289")
 * )
 */
class DepartmentResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var Department $model */
        $model = $this->resource;

        return [
            'id' => $model->id,
            'guid' => $model->guid,
            'name' => $model->name,
            'number' => $model->num,
        ];
    }
}
