<?php

namespace WezomCms\Users\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;
use WezomCms\Catalog\Http\Resources\V1\ProductSimpleResource;
use WezomCms\Orders\Http\Resources\V1\AddressResource;
use WezomCms\Users\Models\User;

/**
 * @OA\Schema(
 *     type="object",
 *     title="User Resource",
 *     description="User Resource",
 * )
 */
class UserResource extends JsonResource
{
    public function toArray($request): array
    {
        /** @var $model User */
        $model = $this;

        return [
            'id' => $model->id,
            'name' => $model->name,
            'surname' => $model->surname,
            'patronymic' => $model->patronymic,
            'phone' => $model->phone,
            'email' => $model->email,
            'lang' => $model->lang,
            'createdAt' => $model->created_at->format(config('cms.core.time.format.created_at.api')),
            'wishlist' => ProductSimpleResource::collection($model->wishlist),
            'ref_id' => $model->ref_id,
            'bonus' => $model->bonus,
            'addresses' => AddressResource::collection($model->addresses),
        ];
    }

    /**
     * @OA\Property(property="id", title="ID", description="ID пользователя", example=1),
     * @OA\Property(property="name", title="Name", description="Имя пользователя", example="Иван"),
     * @OA\Property(property="surname", title="Surname", description="Фамилия пользователя", example="Иванов"),
     * @OA\Property(property="patronymic", title="Patronymic", description="Отчество пользователя", example="Иванович"),
     * @OA\Property(property="phone", title="Phone", description="Телефон пользователя", example="380954545667"),
     * @OA\Property(property="email", title="Email", description="Email пользователя", example="ivan@gmail.com"),
     * @OA\Property(property="lang", title="Language", description="Локаль пользователя", example="kk"),
     * @OA\Property(property="createdAt", title="Created at", description="Дата создания пользователя", example="2022-01-27")
     * @OA\Property(property="wishlist", title="Wishlist", description="Список желаний", type="array",
     *     @OA\Items(ref="#/components/schemas/ProductSimpleResource"))
     * )
     * @OA\Property(property="ref_id", title="Referral", description="ID 'пригласившего' пользователя", example=25)
     * @OA\Property(property="bonus", title="Bonus", description="Накопленные бонусы", example=250)
     * @OA\Property(property="addresses", title="User addresses", description="Адреса доставки", type="array",
     *     @OA\Items(ref="#/components/schemas/AddressResource"))
     * )
     */
}

