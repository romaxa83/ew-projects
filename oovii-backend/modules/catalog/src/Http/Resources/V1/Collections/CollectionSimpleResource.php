<?php

namespace WezomCms\Catalog\Http\Resources\V1\Collections;

use Illuminate\Http\Resources\Json\JsonResource;
use WezomCms\Catalog\Models\Collections\Collection;

/**
 * @OA\Schema(
 *     type="object",
 *     title="Collection simple Resource",
 *     description="Collection simple Resource",
 * )
 */
class CollectionSimpleResource extends JsonResource
{
    public static $wrap = 'user';

    public function toArray($request): array
    {
        /** @var $model Collection */
        $model = $this;

        return [
            'id' => $model->id,
            'name' => $model->translation->name,
            'image' => $model->getImageUrl(),
            'startAt' => $model->start_at ? $model->start_at->format(config('cms.core.time.format.created_at.api')) : null,
            'isStartCounter' => $model->start_counter,
            'endAt' => $model->end_at ? $model->end_at->format(config('cms.core.time.format.created_at.api')) : null,
            'isEndCounter' => $model->end_counter,
            'createdAt' => $model->created_at->format(config('cms.core.time.format.created_at.api')),
            'type' => $model->type,
            'hashData' => $model->hash_data,
        ];
    }

    /**
     * @OA\Property(property="id", title="ID", description="ID коллекции", example=1),
     * @OA\Property(property="name", title="Name", description="Название коллекции", example="Collection for real women"),
     * @OA\Property(property="image", title="Image", description="Ссылка на картинку", example="http://192.168.175.1/storage/collections/medium/lWRZqMXUX0cnKagh73IWyP1exMcasD7Mz5sEHaed.png?v=1643982687"),
     * @OA\Property(property="startAt", title="Start at", description="Дата начало продаж", example="2022-01-27"),
     * @OA\Property(property="isStartCounter", title="Is start counter", description="Выводить счетчик от даты начало продаж", example=true),
     * @OA\Property(property="endAt", title="End at", description="Дата конца продаж", example="2022-01-27"),
     * @OA\Property(property="isEndCounter", title="Is end counter", description="Выводить счетчик от даты конца продаж", example=false),
     * @OA\Property(property="createdAt", title="Created at", description="Дата создания коллекции", example="2022-01-27"),
     * @OA\Property(property="type", title="Type", description="Тип коллекции, потдерживает - [soon, stock, actual]", example="soon"),
     * @OA\Property(property="hashData", title="Hash data", description="Хеш данных (startAt, endAt), для определения изменения данных", example="07900e600cd6b255e7fd5f7d0ef234ea"),
     */
}

