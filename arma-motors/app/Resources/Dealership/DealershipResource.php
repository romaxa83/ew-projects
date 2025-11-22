<?php

namespace App\Resources\Dealership;

use App\Models\Dealership\Dealership;
use Illuminate\Http\Resources\Json\JsonResource;

class DealershipResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var Dealership $model */
        $model = $this->resource;

        return [
            'id' => $model->id,
            'name' => $model->current->name,
            'alias' => $model->alias,
        ];
    }
}
