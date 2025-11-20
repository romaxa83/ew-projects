<?php

namespace App\Resources\JD;

use App\Helpers\DateFormat;
use App\Models\JD\ModelDescription;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(type="object", title="ModelDescription Resource",
 *     @OA\Property(property="id", type="integer", description="ID", example=1),
 *     @OA\Property(property="jd_id", type="integer", description="ID из BOED", example=22),
 *     @OA\Property(property="eg_jd_id", type="integer", description="ID equimpment-group из BOED", example=3),
 *     @OA\Property(property="name", type="string", description="Название", example="AT ACTIVATION KEY"),
 *     @OA\Property(property="status", type="boolean", description="Status", example=true),
 *     @OA\Property(property="created", type="string", description="Создание", example="22.06.2020 10:04"),
 *     @OA\Property(property="updated", type="string", description="Обновление", example="22.06.2020 10:04"),
 *     @OA\Property(property="size", type="integer", description="Размер (устанавливается в BOED)", example=4490),
 *     @OA\Property(property="size_parameter", type="string", description="Параметр размера (устанавливается в BOED)", example="kg"),
 *     @OA\Property(property="type", type="string", description="Тип (устанавливается в BOED)", example="1"),
 * )
 */
class ModelDescriptionResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var ModelDescription $md */
        $md = $this;

        return [
            'id' => $md->id,
            'jd_id' => $md->jd_id,
            'eg_jd_id' => $md->eg_jd_id,
            'name' => $md->name,
            'status' => $md->status,
            'created' => DateFormat::front($md->created_at),
            'updated' => DateFormat::front($md->updated_at),
            'size' => $md->product->size_name ?? null,
            'size_parameter' => isset($md->product->sizeParameter)
                ? $md->product->sizeParameter->name
                : null,
            'type' => $md->product->type ?? null,
        ];
    }
}
