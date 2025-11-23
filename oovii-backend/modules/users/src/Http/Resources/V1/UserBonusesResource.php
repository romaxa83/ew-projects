<?php

namespace WezomCms\Users\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;
use WezomCms\Users\Models\User;

/**
 * @OA\Schema(
 *     type="object",
 *     title="User bonus Resource",
 *     description="User bonuses data",
 * )
 */
class UserBonusesResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var $model User */
        $model = $this;

        return [
            'id' => $model->id,
            'total_plus' => $model->getPositiveBonusSum(),
            'total_minus' => $model->getNegativeBonusSum(),
            'bonus' => $model->bonus,
            'bonus_history' => BonusHistoryResource::collection($model->inviterBonusHistory),
        ];
    }

    /**
     * @OA\Property(property="id", title="ID", description="ID пользователя", example=1),
     * @OA\Property(property="total_plus", title="Total bonus plus", description="Общая сумма начисленных бонусов", example=1486),
     * @OA\Property(property="total_minus", title="Total bonus minus", description="Общая сумма потраченных бонусов", example=763),
     * @OA\Property(property="bonus", title="Bonus sum", description="Сумма доступных бонусов", example=723),
     * @OA\Property(property="bonus_history", title="Bonus history", description="История начисления/использования бонусов", type="array",
     *     @OA\Items(ref="#/components/schemas/BonusHistoryResource"))
     * )
     */
}

