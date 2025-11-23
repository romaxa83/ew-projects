<?php

namespace WezomCms\Orders\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;
use WezomCms\Orders\Models\Payment;

/**
 * @OA\Schema(
 *     type="object",
 *     title="Payment Resource",
 *     description="Payment Resource",
 * )
 */
class PaymentResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var $model Payment */
        $model = $this;

        return [
            'id' => $model->id,
            'name' => $model->translation->name,
            'sort' => $model->sort,
            'driver' => $model->driver,
            'icon' => $model->icon,
        ];
    }

    /**
     * @OA\Property(property="id", title="ID", description="ID способа оплаты", example=1),
     * @OA\Property(property="name", title="Name", description="Название способа оплаты", example="PayBox Money (картой)"),
     * @OA\Property(property="sort", title="Sort", description="Сортировка способов оплаты", example="1"),
     * @OA\Property(property="driver", title="Driver способа оплаты", description="Driver способа оплаты", example="pay-box"),
     * @OA\Property(property="icon", title="Icon", description="Изображение (иконка)", example="images/small/pay-box.png"),
     */
}
