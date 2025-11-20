<?php

namespace App\Resources\Country;

use App\Models\User\Nationality;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(type="object", title="Nationality Resource",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Albanian"),
 *     @OA\Property(property="alias", type="string", example="AL"),
 * )
 */
class NationalityResource extends JsonResource
{
    public function toArray($request):array
    {
        /** @var Nationality $model */
        $model = $this;

        return [
            'id' => $model->id,
            'name' => $model->name,
            'alias' => $model->alias
        ];
    }
}
