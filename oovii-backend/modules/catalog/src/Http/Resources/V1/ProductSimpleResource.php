<?php

namespace WezomCms\Catalog\Http\Resources\V1;

use Carbon\CarbonImmutable;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use WezomCms\Catalog\Models\Collections\Collection;
use WezomCms\Catalog\Models\Product;

/**
 * @OA\Schema(
 *     type="object",
 *     title="Product Resource (simple)",
 *     description="Product Resource (simple)",
 * )
 */
class ProductSimpleResource extends BaseProductResource
{
    public function toArray($request): array
    {
        /** @var $model Product */
        $model = $this;

        return [
            'id' => $model->id,
            'name' => $model->translation->name,
            'brand' => BrandResource::make($model->brand),
            'category' => CategoryResource::make($model->category),
            'cost' => $model->cost,
            'costDiscount' => $model->cost_discount ?: null,
            'amount' => $model->amount,
            'amountOneUser' => $model->amount_one_user,
            'image' => $model->getImageUrl('medium'),
            'likes' => $model->likes,
            'dislikes' => $model->dislikes,
            'createdAt' => $model->created_at->format(config('cms.core.time.format.created_at.api')),
            'publishedAt' => $model->published_at
                ? $model->published_at->format(config('cms.core.time.format.created_at.api'))
                : null,
            'expiresAt' => $model->expires_at
                ? $model->expires_at->format(config('cms.core.time.format.created_at.api'))
                : null,
            'flags' => $model->flags,
            'collections' => $model->collections->implode('name', ', '),
            'collectionData' => $this->getCollectionData()
        ];
    }

    /**
     *  @OA\Property(property="id", title="ID", description="ID товара", example=1),
     *  @OA\Property(property="name", title="Name", description="Название товара", example="Product for real women"),
     *  @OA\Property(property="brand", title="Brand", description="Бренд", type="object",
     *      ref="#/components/schemas/BrandResource"
     *  )
     *  @OA\Property(property="category", title="Category", description="Категория", type="object",
     *      ref="#/components/schemas/CategoryResource"
     *  )
     *  @OA\Property(property="image", title="Image", description="Ссылка на главную картинку",
     *      example="http://192.168.175.1/storage/products/images/small/6hlcMTfi075vHncJFwLJzgoXwCnwU3Xsvtr0e7fK.png?v=1644395573")
     *  @OA\Property(property="cost", title="Cost", description="Цена", example=12.90,
     *     oneOf={
     *             @OA\Schema(type="integer"),
     *             @OA\Schema(type="float")
     *           },
     *  ))
     *  @OA\Property(property="costDiscount", title="Cost discount", description="Цена со скидкой",
     *     oneOf={
     *             @OA\Schema(type="integer"),
     *             @OA\Schema(type="float")
     *           },
     *  ))
     *  @OA\Property(property="amount", title="Amount", description="Кол-во", example=10)
     *  @OA\Property(property="amountOneUser", title="Amount one user",
     *      description="Кол-во для одного пользователя", example=5)
     *  @OA\Property(property="likes", title="Likes", description="Все лайки по отзывам", example=13)
     *  @OA\Property(property="dislikes", title="Dislikes", description="Все дизлайки по отзывам", example=3)
     *  @OA\Property(property="createdAt", title="Created at", description="Дата создания", example="2022-01-27")
     *  @OA\Property(property="publishedAt", title="Published at", description="Дата публикации", example="2022-01-27")
     *  @OA\Property(property="expiresAt", title="Expires at", description="Дата окончания продаж", example="2022-01-27")
     *  @OA\Property(property="flags", title="Product flags", description="Метки товара", type="array",
     *     @OA\Items(
     *         @OA\Property(property="name", title="Name", description="Название метки", example="best_price"),
     *         @OA\Property(property="color", title="Color", description="HEX цвет метки", example="#E6F7F9"),
     *         @OA\Property(property="text", title="Text", description="Текст метки", example="Лучшая цена"),
     *     )
     *  ),
     *  @OA\Property(property="collectionData", title="Collection Data", description="Данные по коллекции, данные есть если выходить на продукт из коллекции", type="object",
     *      @OA\Property(property="id", title="ID", description="ID коллекции", example="27"),
     *      @OA\Property(property="startAt", title="Start at", description="Дата начало продаж", example="2022-01-27"),
     *      @OA\Property(property="endAt", title="Start at", description="Дата конца продаж", example="2022-01-27"),
     *      @OA\Property(property="isReady", title="Is Ready", description="Стартанула ли уже коллекция", example=true),
     *  )
     */
}
