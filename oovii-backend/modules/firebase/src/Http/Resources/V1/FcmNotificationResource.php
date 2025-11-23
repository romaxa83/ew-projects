<?php

namespace WezomCms\Firebase\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;
use WezomCms\Firebase\Models\FcmNotification;

/**
 * @OA\Schema(
 *     type="object",
 *     title="Firebase notification resource",
 *     description="Firebase notification resource",
 * )
 */
class FcmNotificationResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var $model FcmNotification */
        $model = $this;
        return [
            'id' => $model->id,
            'status' => $model->status,
            'type' => $model->type,
            'entityType' => $this->prettyEntityType($model->entity_type),
            'entityId' => $model->entity_id,
            'title' => $model->send_data['title'] ?? null,
            'text' => $model->send_data['text'] ?? null,
            'createdAt' => $model->created_at->format(config('cms.core.time.format.created_at.api')),
        ];
    }

    /**
     *  @OA\Property(property="id", title="ID", description="ID", example=1)
     *  @OA\Property(property="status", title="Status", description="Статус, потдерживает - [crteated, send]", example="send")
     *  @OA\Property(property="type", title="Type", description="Тип, потдерживает - [test]", example="test")
     *  @OA\Property(property="entityType", title="Entity type", description="Связаная модель", example="Collection")
     *  @OA\Property(property="entityId", title="Entity id", description="ID Связанной модели", example=1)
     *  @OA\Property(property="title", title="Title", description="Заголовок уведомления", example="some title")
     *  @OA\Property(property="text", title="Text", description="Текст уведомления", example="some text")
     *  @OA\Property(property="createdAt", title="Created at", description="Дата создания", example="2022-01-27")
     */

    protected function prettyEntityType($value)
    {
        if($value){
            return last(explode("\\", $value));
        }

        return $value;
    }
}
