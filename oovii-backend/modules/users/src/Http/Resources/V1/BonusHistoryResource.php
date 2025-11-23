<?php

namespace WezomCms\Users\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;
use WezomCms\Users\Models\BonusHistory;

/**
 * @OA\Schema(
 *     type="object",
 *     title="Bonus History Resource",
 *     description="Bonus history resource",
 * )
 */
class BonusHistoryResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var $model BonusHistory */
        $model = $this;

        return [
            'id' => $model->id,
            'title' => $model->getFrontTitle(),
            'positive' => $model->isPositive(),
            'bonus' => $model->bonus,
            'created_at' => $model->created_at->format('d.m.Y'),
        ];
    }

    /**
     * @OA\Property(property="id", title="ID", description="ID транзанкии", example=125),
     * @OA\Property(property="title", title="Title", description="Наименование транзакции (имя реферала/номер заказа)", example="Петренко Сергей"),
     * @OA\Property(property="positive", title="Positive", description="Начисление/расход", example=true),
     * @OA\Property(property="bonus", title="Bonus", description="Сумма начисления/списания бонусов", example=896),
     * @OA\Property(property="created_at", title="Date", description="Дата транзакции", example="18.03.2022"),
     */
}

