<?php

namespace WezomCms\Catalog\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;
use WezomCms\Catalog\Http\Resources\V1\Collections\CollectionSimpleResource;
use WezomCms\Catalog\Models\Product;
use WezomCms\Catalog\Models\Specifications\SpecValue;
use WezomCms\Catalog\Repositories\ProductRepository;
use WezomCms\Core\Http\Resources\V1\AdministratorSimpleResource;
use WezomCms\ProductReviews\Http\Resources\V1\ReviewResource;
use WezomCms\Providers\Http\Resources\V1\ProviderSimpleResource;

/**
 * @OA\Schema(
 *     type="object",
 *     title="Product Resource",
 *     description="Product Resource",
 * )
 */
class ProductResource extends BaseProductResource
{
    public function toArray($request): array
    {
        /** @var $model Product */
        $model = $this;

        $this->groupAttribute($model->group_key, $model->id);

        return [
            'id' => $model->id,
            'name' => $model->translation->name,
            'brand' => BrandResource::make($model->brand),
            'category' => CategoryResource::make($model->category),
            'description' => $model->translation->description,
            'features' => [
                $model->translation->feature_1,
                $model->translation->feature_2,
                $model->translation->feature_3,
            ],
            'likes' => $model->likes,
            'dislikes' => $model->dislikes,
            'labels' => $model->labelsArray(),
            'cost' => $model->cost,
            'costDiscount' => $model->cost_discount ?: null,
            'amount' => $model->amount,
            'amountOneUser' => $model->amount_one_user,
            'image' => $model->getImageUrl('big'),
            'images' => $model->imagesUrlArray('big'),
            'groupKey' => $model->group_key,
            'provider' => ProviderSimpleResource::make($model->providerProfile),
            'moderator' => AdministratorSimpleResource::make($model->moderator),
            'reviews' => ReviewResource::collection($model->rootReviews),
            'products' => ProductSimpleResource::collection($model->availableRelationsActiveCollection),
//            'products' => ProductSimpleResource::collection($model->availableRelations),
            'attributes' => SpecValueResource::collection($model->publishedSpecifications),
            'attributesGroup' => $this->groupAttribute($model->group_key, $model->id),
            'collections' => CollectionSimpleResource::collection($model->collections),
            'createdAt' => $model->created_at->format(config('cms.core.time.format.created_at.api')),
            'publishedAt' => $model->published_at
                ? $model->published_at->format(config('cms.core.time.format.created_at.api'))
                : null,
            'expiresAt' => $model->expires_at
                ? $model->expires_at->format(config('cms.core.time.format.created_at.api'))
                : null,
            'flags' => $model->flags,
            'collectionData' => $this->getCollectionData()
        ];
    }

    private function groupAttribute($groupKey, $withoutID): array
    {
        $temp = [];

        if ($groupKey) {
            $models = app(ProductRepository::class)
                ->getProductsByGroupKey($groupKey, ['publishedSpecifications'], $withoutID)
                ->filter(fn (Product $product) => $product->availableForPurchase());

            foreach ($models as $model) {
                /** @var $model Product */
                foreach ($model->publishedSpecifications as $spec) {
                    /** @var $spec SpecValue */
                    $tmp = [
                        'id' => $spec->specification->id,
                        'name' => $spec->specification->translation->name ?? null,
                        'type' => $spec->specification->type ?? null,
                        'slug' => $spec->specification->slug ?? null,
                        'valueId' => $spec->id,
                        'value' => $spec->translation->name ?? null,
                        'productId' => $model->availableForPurchase() ? $model->id : null
                    ];

                    if ($spec->specification->isColor()) {
                        $tmp['color'] = $spec->color;
                    }

                    $temp[] = $tmp;
                }
            }

        }

        return $temp;
    }

    /**
     *  @OA\Property(property="id", title="ID", description="ID товара", example=1),
     *  @OA\Property(property="name", title="Name", description="Название товара", example="Product for real women")
     *  @OA\Property(property="brand", title="Brand", description="Бренд", type="object",
     *      ref="#/components/schemas/BrandResource"
     *  )
     *  @OA\Property(property="category", title="Category", description="Категория", type="object",
     *      ref="#/components/schemas/CategoryResource"
     *  )
     *  @OA\Property(property="description", title="Description", description="Описание", example="Product description")
     *  @OA\Property(property="features", title="Features", type="array",  @OA\Items( description="Особенности продукта (три записи, может быть null)",
     *     oneOf={
     *             @OA\Schema(type="string"),
     *             @OA\Schema(type="null")
     *           },
     *  ))
     *  @OA\Property(property="likes", title="Likes", description="Все лайки по отзывам", example=13)
     *  @OA\Property(property="dislikes", title="Dislikes", description="Все дизлайки по отзывам", example=3)
     *  @OA\Property(property="labels", title="Labels", type="array",
     *      description="Массив лейблов, типа хит продаж",  @OA\Items(type="string"))
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
     *  @OA\Property(property="image", title="Image", description="Ссылка на главную картинку",
     *      example="http://192.168.175.1/storage/products/images/small/6hlcMTfi075vHncJFwLJzgoXwCnwU3Xsvtr0e7fK.png?v=1644395573")
     *  @OA\Property(property="groupKey", title="Group key", description="Ключ группы", example="some key")
     *  @OA\Property(property="provider", title="Provider", description="Поставщик", type="object",
     *      ref="#/components/schemas/ProviderSimpleResource"
     *  )
     *  @OA\Property(property="moderator", title="Moderator", description="Модератор", type="object",
     *      ref="#/components/schemas/AdministratorSimpleResource"
     *  )
     *  @OA\Property(property="reviews", title="Reviews", description="Отзывы", type="array",
     *      @OA\Items(ref="#/components/schemas/ReviewResource"))
     *  )
     *  @OA\Property(property="products", title="Products", description="Привязанные товары", type="array",
     *      @OA\Items(ref="#/components/schemas/ProductSimpleResource"))
     *  )
     *  @OA\Property(property="attributes", title="Attributes", description="Характеристики", type="array",
     *      @OA\Items(ref="#/components/schemas/SpecValueResource"))
     *  )
     *  @OA\Property(property="attributesGroup", title="Attributes group", description="Характеристики всех товаров данной группы, не включая данный товар", type="array",
     *      @OA\Items(
     *          @OA\Property(property="id", title="ID", description="ID", example=1),
     *          @OA\Property(property="name", title="Name", description="Название характеристики", example="Color"),
     *          @OA\Property(property="type", title="Type", description="Тип пока потдерживаеться [color]", example="сolor"),
     *          @OA\Property(property="slug", title="Slug", description="Слаг характеристики", example="ves"),
     *          @OA\Property(property="color", title="Color", description="HEX цвет (присутствует у характ. с типом color)", example="#FFFF00"),
     *          @OA\Property(property="valueId", title="Value ID", description="ID", example=1),
     *          @OA\Property(property="value", title="Value", description="Название значения характеристики", example="grey"),
     *          @OA\Property(property="productId", title="Product id", description="ID товара, данной характеристики, если у товара кол-во = 0, будет null", example=1)
     *      )
     *  )
     *  @OA\Property(property="collections", title="Collections", description="Коллекции в которыз находится товар", type="array",
     *      @OA\Items(ref="#/components/schemas/CollectionSimpleResource"))
     *  )
     *  @OA\Property(property="createdAt", title="Created at", description="Дата создания", example="2022-01-27")
     *  @OA\Property(property="publishedAt", title="Published at", description="Дата публикации", example="2022-01-27")
     *  @OA\Property(property="expiresAt", title="Expires at", description="Дата окончания продаж", example="2022-01-27")
     *  @OA\Property(property="flags", title="Product flags", description="Метки товара", type="array",
     *      @OA\Items(
     *          @OA\Property(property="name", title="Name", description="Название метки", example="best_price"),
     *          @OA\Property(property="color", title="Color", description="HEX цвет метки", example="#E6F7F9"),
     *          @OA\Property(property="text", title="Text", description="Текст метки", example="Лучшая цена"),
     *      )
     *  )
     *  @OA\Property(property="collectionData", title="Collection Data", description="Данные по коллекции, данные есть если выходить на продукт из коллекции", type="object",
     *      @OA\Property(property="id", title="ID", description="ID коллекции", example="27"),
     *      @OA\Property(property="startAt", title="Start at", description="Дата начало продаж", example="2022-01-27"),
     *      @OA\Property(property="endAt", title="Start at", description="Дата конца продаж", example="2022-01-27"),
     *      @OA\Property(property="isReady", title="Is Ready", description="Стартанула ли уже коллекция", example=true),
     *  )
     */

}
