<?php

namespace WezomCms\Orders\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;
use WezomCms\Orders\Models\Order;

/**
 * @OA\Schema(
 *     type="object",
 *     title="Order Recipient Resource",
 *     description="Order Recipient Resource",
 * )
 */
class OrderRecipientResource extends JsonResource
{
    public function toArray($request)
    {
        /** @var Order $model */
        $model = $this;
        $client = $model->getCustomer();

        return [
            'id' => $model->recipient->id,
            'recipient_is_me' => $model->recipient->recipient_is_me,
            'name' => $client->getName(),
            'surname' => $client->getSurname(),
            'phone' => $client->getPhone(),
            'email' => $client->getEmail(),
            'comment' => $model->recipient->comment,
        ];
    }

    /**
     * @OA\Property(property="id", title="ID", description="ID адреса", example=1),
     * @OA\Property(property="recipient_is_me", title="Recipient is me", description="Получатель - я", example=false),
     * @OA\Property(property="name", title="Name", description="Имя получателя", example="Станислав"),
     * @OA\Property(property="surname", title="Surname", description="Фамилия получателя", example="Николаенко"),
     * @OA\Property(property="phone", title="Phone", description="Телефон получателя", example="+77052312342"),
     * @OA\Property(property="email", title="Email", description="Email получателя", example="st_nik@gmail.com"),
     * @OA\Property(property="comment", title="Comment", description="Комментарий к заказу", example="Комментарий к заказу..."),
     */
}
