<?php

namespace App\Resources\Service;

use App\Models\Catalogs\Service\Service;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceItemResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var Service $model */
        $model = $this->resource;

        return [
            'id' => $model->id,
            'alias' => $model->alias,
            'name' => $model->current->name
        ];
    }
}
