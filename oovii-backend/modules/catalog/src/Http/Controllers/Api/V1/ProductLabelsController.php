<?php

namespace WezomCms\Catalog\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;
use Throwable;
use WezomCms\Catalog\Http\Resources\V1\LabelResource;
use WezomCms\Catalog\Repositories\LabelRepository;
use WezomCms\Core\Http\Controllers\ApiController;
use WezomCms\Core\Traits\GetModelFilterWithProductsTrait;

class ProductLabelsController extends ApiController
{
    use GetModelFilterWithProductsTrait;

    protected array $orderBySupport = ['id', 'sort', 'is_gender'];

    public function __construct(
        protected LabelRepository $repo,
    )
    {
        parent::__construct();
    }

    /**
     * @OA\Get (
     *     path="/mobile/product-labels",
     *     tags={"Product"},
     *     summary="Get product labels list",
     *     @OA\Parameter(name="product_name", in="query", required=false,
     *         description="Поиск по имени товара",
     *         @OA\Schema(type="string", example="test")
     *     ),
     *     @OA\Parameter(name="all", in="query", required=false,
     *         description="Все метки (не только на опубликованных товарах)",
     *         @OA\Schema(type="boolean", example=false)
     *     ),
     *     @OA\Parameter(name="is_gender", in="query", required=false,
     *         description="Фильтр по пренадлежности",
     *         @OA\Schema(type="boolean", example=true)
     *     ),
     *     @OA\Parameter(name="collection_id", in="query", required=false,
     *         description="Получение тех лейблов, которые присвоены товарам в данной коллекции",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(name="category_id", in="query", required=false,
     *         description="Получение тех лейблов, которые присвоены товарам в данной категории",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(name="brand_id", in="query", required=false,
     *         description="Получение тех лейблов, которые присвоены к товарам заданных брендов",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(name="price_from", in="query", required=false,
     *         description="Фильтр лейблов по максимальной цене привязанных товара",
     *         @OA\Schema(type="integer",example=13700)
     *     ),
     *     @OA\Parameter(name="price_to", in="query", required=false,
     *         description="Фильтр лейблов по минимальной цене привязанных товара",
     *         @OA\Schema(type="integer",example=137)
     *     ),
     *     @OA\Parameter(name="specifications", in="query", required=false,
     *         description="Фильтр лейблов по значениями характеристик привязанных товаров",
     *         @OA\Schema(type="array",
     *              @OA\Items(type="integer")
     *         )
     *     ),
     *     @OA\Parameter(name="order_by", in="query", required=false,
     *         description="По какому полю сортировать, поддерживается - [id, sort, is_gender], если нужно передать несколько полей то так - ?order_by[]=id&order_by[]=sort",
     *         @OA\Schema(type="string", example="id")
     *     ),
     *     @OA\Parameter(name="order_type", in="query", required=false,
     *         description="Тип сортировки, поддерживается - [asc, desc],по умолчанию используеться desc, если передается несколько полей для сортировки, то для каждого, последовательно, можно передовать тип - ?order_type[]=asc&order_type[]=desc",
     *         @OA\Schema(type="string", example="asc")
     *     ),
     *
     *     @OA\Response(response="200", description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="data", type="object",
     *                  ref="#/components/schemas/LabelResource"
     *              ),
     *              @OA\Property(property="success", title="Success", example=true),
     *              @OA\Property(property="code", title="Code", example=0),
     *          )
     *     ),
     *
     *     @OA\Response(response="401", description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request): JsonResponse
    {
        try {
            $models = $this->repo->getAll(
                [],
                false,
                $this->getFilters($request),
                true,
                $this->orderDataForQuery()
            );

            return self::successJsonMessage(LabelResource::collection($models));
        } catch (Throwable $e) {
            Log::error($e->getMessage());

            return self::errorJsonMessage($e->getMessage());
        }
    }
}
