<?php

namespace WezomCms\Catalog\Http\Controllers\Api\V1;

use Auth;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use InvalidArgumentException;
use Log;
use Throwable;
use WezomCms\Catalog\Http\Resources\V1\ProductResource;
use WezomCms\Catalog\Http\Resources\V1\ProductSimpleResource;
use WezomCms\Catalog\Models\Product;
use WezomCms\Catalog\Repositories\CollectionRepository;
use WezomCms\Catalog\Repositories\ProductRepository;
use WezomCms\Core\Http\Controllers\ApiController;
use WezomCms\Users\Models\User;
use WezomCms\Users\Services\UserService;

class ProductController extends ApiController
{
    protected array $orderBySupport = [
        'id',
        'cost',
        'cost_discount',
        'sort',
        'created_at',
        'likes',
        'price',
    ];

    public function __construct(
        protected ProductRepository $repo,
        protected CollectionRepository $collectionRepository,
        protected UserService $userService
    )
    {
        parent::__construct();
    }

    /**
     * @OA\Get (
     *     path="/mobile/products",
     *     tags={"Product"},
     *     summary="Get product list",
     *     @OA\Parameter(name="search", in="query", required=false,
     *         description="Поиск по имени товара",
     *         @OA\Schema(type="string",example="test")
     *     ),
     *     @OA\Parameter(name="collection_id", in="query", required=false,
     *         description="Товары относящиеся к коллекции",
     *         @OA\Schema(type="integer",example=1)
     *     ),
     *     @OA\Parameter(name="label_id", in="query", required=false,
     *         description="Фильтр товаров по лейблу, лейблы получаем по запросу /mobile/product-labels, если несколько значений, то передаем так - ?label_id[]=1&label_id[]=2",
     *         @OA\Schema(type="integer",example=1)
     *     ),
     *     @OA\Parameter(name="brand_id", in="query", required=false,
     *         description="Фильтр товаров по бренду, бренд получаем по запросу /mobile/brands, если несколько значений, то передаем так - ?brand_id[]=1&brand_id[]=2",
     *         @OA\Schema(type="integer",example=1)
     *     ),
     *     @OA\Parameter(name="category_id", in="query", required=false,
     *         description="Фильтр товаров по категории, категорию получаем по запросу /mobile/categories, если несколько значений, то передаем так - ?category_id[]=1&category_id[]=2",
     *         @OA\Schema(type="integer",example=1)
     *     ),
     *     @OA\Parameter(name="spec_value_id", in="query", required=false,
     *         description="Фильтр товаров по значениями характеристик, характеристики со значениями получаем по запросу /mobile/specifications, если несколько значений, то передаем так - ?spec_value_id[]=1&spec_value_id[]=2",
     *         @OA\Schema(type="integer",example=1)
     *     ),
     *     @OA\Parameter(name="specifications", in="query", required=false,
     *         description="Фильтр товаров по значениями характеристик, формат - ?specifications[{specification_id}][]={value1_id}&specifications[{specification_id}][]={value2_id}",
     *         @OA\Schema(type="array",
     *              @OA\Items(type="integer")
     *         )
     *     ),
     *     @OA\Parameter(name="id", in="query", required=false,
     *         description="Фильтр товаров по ID товара, если несколько значений, то передаем так - ?id[]=1&id[]=2",
     *         @OA\Schema(type="integer",example=1)
     *     ),
     *     @OA\Parameter(name="all", in="query", required=false,
     *         description="Все товары (без учета наличия, сроков продажи и т.д.)",
     *         @OA\Schema(type="boolean", example=false)
     *     ),
     *     @OA\Parameter(name="cost_from", in="query", required=false,
     *         description="Фильтр по максимальной цене товара",
     *         @OA\Schema(type="integer",example=13700)
     *     ),
     *     @OA\Parameter(name="cost_to", in="query", required=false,
     *         description="Фильтр по минимальной цене товара",
     *         @OA\Schema(type="integer",example=137)
     *     ),
     *     @OA\Parameter(name="price_from", in="query", required=false,
     *         description="Фильтр по максимальной цене товара (с учетом скидки)",
     *         @OA\Schema(type="integer",example=13700)
     *     ),
     *     @OA\Parameter(name="price_to", in="query", required=false,
     *         description="Фильтр по минимальной цене товара (с учетом скидки)",
     *         @OA\Schema(type="integer",example=137)
     *     ),
     *     @OA\Parameter(name="order_by", in="query", required=false,
     *         description="По какому полю сортировать, поддерживается - [id, sort, cost, cost_discount, created_at, likes, price], если нужно передать несколько полей то так - ?order_by[]=id&order_by[]=sort",
     *         @OA\Schema(type="string",example="id")
     *     ),
     *     @OA\Parameter(name="order_type", in="query", required=false,
     *         description="Тип сортировки, поддерживается - [asc, desc],по умолчанию используеться desc, если передается несколько полей для сортировки, то для каждого, последовательно, можно передовать тип - ?order_type[]=asc&order_type[]=desc",
     *         @OA\Schema(type="string",example="asc")
     *     ),
     *
     *     @OA\Response(response="200", description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="data", type="object",
     *                  ref="#/components/schemas/ProductSimpleResource"
     *              ),
     *              @OA\Property(property="success", title="Success", example=true),
     *              @OA\Property(property="code", title="Code", example=0)
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
            $models = $this->repo->getAll([
                'images',
                'publishedReviews',
                'brand',
                'category',
            ],
                false,
                $this->getProductFilter($request),
                true,
                $this->orderDataForQuery()
            );

            $collectionData = [];
            if($collectionID = $request['collection_id']){
                $collection = $this->collectionRepository->getByID($collectionID);
                $collectionData = Product::formatCollectionDataForProduct($collection);
            }

            return self::successJsonMessage(
                ProductSimpleResource::collection($models)->setCollectionData($collectionData)
            );
        } catch (Throwable $e) {
            Log::error($e->getMessage());
            return self::errorJsonMessage($e->getMessage());
        }
    }

    /**
     * @OA\Get (
     *     path="/mobile/products-count",
     *     tags={"Product"},
     *     summary="Get count products for filter",
     *     @OA\Parameter(name="search", in="query", required=false,
     *         description="Поиск по имени товара",
     *         @OA\Schema(type="string",example="test")
     *     ),
     *     @OA\Parameter(name="collection_id", in="query", required=false,
     *         description="Товары относящиеся к коллекции",
     *         @OA\Schema(type="integer",example=1)
     *     ),
     *     @OA\Parameter(name="label_id", in="query", required=false,
     *         description="Фильтр товаров по лейблу, лейблы получаем по запросу /mobile/product-labels, если несколько значений, то передаем так - ?label_id[]=1&label_id[]=2",
     *         @OA\Schema(type="integer",example=1)
     *     ),
     *     @OA\Parameter(name="brand_id", in="query", required=false,
     *         description="Фильтр товаров по бренду, бренд получаем по запросу /mobile/brands, если несколько значений, то передаем так - ?brand_id[]=1&brand_id[]=2",
     *         @OA\Schema(type="integer",example=1)
     *     ),
     *     @OA\Parameter(name="category_id", in="query", required=false,
     *         description="Фильтр товаров по категории, категорию получаем по запросу /mobile/categories, если несколько значений, то передаем так - ?category_id[]=1&category_id[]=2",
     *         @OA\Schema(type="integer",example=1)
     *     ),
     *     @OA\Parameter(name="spec_value_id", in="query", required=false,
     *         description="Фильтр товаров по значениями характеристик, характеристики со значениями получаем по запросу /mobile/specifications, если несколько значений, то передаем так - ?spec_value_id[]=1&spec_value_id[]=2",
     *         @OA\Schema(type="integer",example=1)
     *     ),
     *     @OA\Parameter(name="specifications", in="query", required=false,
     *         description="Фильтр товаров по значениями характеристик, формат - ?specifications[{specification_id}][]={value1_id}&specifications[{specification_id}][]={value2_id}",
     *         @OA\Schema(type="array",
     *              @OA\Items(type="integer")
     *         )
     *     ),
     *     @OA\Parameter(name="id", in="query", required=false,
     *         description="Фильтр товаров по ID товара, если несколько значений, то передаем так - ?id[]=1&id[]=2",
     *         @OA\Schema(type="integer",example=1)
     *     ),
     *     @OA\Parameter(name="all", in="query", required=false,
     *         description="Все товары (без учета наличия, сроков продажи и т.д.)",
     *         @OA\Schema(type="boolean", example=false)
     *     ),
     *     @OA\Parameter(name="cost_from", in="query", required=false,
     *         description="Фильтр по максимальной цене товара",
     *         @OA\Schema(type="integer",example=13700)
     *     ),
     *     @OA\Parameter(name="cost_to", in="query", required=false,
     *         description="Фильтр по минимальной цене товара",
     *         @OA\Schema(type="integer",example=137)
     *     ),
     *     @OA\Parameter(name="price_from", in="query", required=false,
     *         description="Фильтр по максимальной цене товара (с учетом скидки)",
     *         @OA\Schema(type="integer",example=13700)
     *     ),
     *     @OA\Parameter(name="price_to", in="query", required=false,
     *         description="Фильтр по минимальной цене товара (с учетом скидки)",
     *         @OA\Schema(type="integer",example=137)
     *     ),
     *     @OA\Parameter(name="order_by", in="query", required=false,
     *         description="По какому полю сортировать, поддерживается - [id, sort, cost, cost_discount, created_at, likes], если нужно передать несколько полей то так - ?order_by[]=id&order_by[]=sort",
     *         @OA\Schema(type="string",example="id")
     *     ),
     *     @OA\Parameter(name="order_type", in="query", required=false,
     *         description="Тип сортировки, поддерживается - [asc, desc],по умолчанию используеться desc, если передается несколько полей для сортировки, то для каждого, последовательно, можно передовать тип - ?order_type[]=asc&order_type[]=desc",
     *         @OA\Schema(type="string",example="asc")
     *     ),
     *
     *     @OA\Response(response="200", description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="data", description="Кол-во товаров, для фильтра", example=0),
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
    public function count(Request $request): JsonResponse
    {
        try {
            return self::successJsonMessage(
                $this->repo->countAllWithFilter($this->getProductFilter($request))
            );
        } catch (Throwable $e) {
            Log::error($e->getMessage());
            return self::errorJsonMessage($e->getMessage());
        }
    }

    /**
     * @OA\Get (
     *     path="/mobile/products/{id}",
     *     tags={"Product"},
     *     summary="Get product",
     *
     *     @OA\Parameter(name="id", in="path", required=true,
     *         description="Идентификатор товара",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Response(response="200", description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="data", type="object",
     *                  ref="#/components/schemas/ProductResource"
     *              ),
     *              @OA\Property(property="success", title="Success", example=true),
     *              @OA\Property(property="code", title="Code", example=0),
     *          )
     *     ),
     *
     *     @OA\Response(response="401", description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     * @param $productID
     * @return JsonResponse
     */
    public function show(Request $request, $productID): JsonResponse
    {
        try {
            $model = $this->repo->getByID($productID, [
                'publishedReviews.publishedChildren',
                'providerProfile',
                'moderator',
                'labels.translation',
                'relations',
                'images',
                'publishedSpecifications.translation',
                'publishedSpecifications.specification.translation',
                'collections',
                'brand',
                'category',
            ]);

            return self::successJsonMessage(ProductResource::make($model));
        } catch (Throwable $e) {
            Log::error($e->getMessage());
            return self::errorJsonMessage($e->getMessage());
        }
    }

    /**
     * @OA\Post (
     *     path="/mobile/products/{id}/add-to-wishlist",
     *     security={{"Basic": {}}},
     *     tags={"Product"},
     *     summary="Add a product to an auth user`s wishlist",
     *
     *     @OA\Parameter(name="id", in="path", required=true,
     *         description="Идентификатор товара",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Parameter(name="collection_id", in="query", required=false,
     *         description="ID коллекции, для понимания с какой коллекции добавлен товар",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Response(response="200", description="OK",
     *          @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *      ),
     *
     *     @OA\Response(response="401", description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     * @param int $productID
     * @return JsonResponse
     */
    public function addToWishlist(Request $request, int $productID): JsonResponse
    {
        /** @var $user User */
        $user = Auth::user();
        try {
            if(!$this->repo->existBy('id', $productID)){
                throw new InvalidArgumentException(
                    __('cms-catalog::admin.products.exception.not found by id', [
                        "id" => $productID
                    ]),
                    Response::HTTP_BAD_REQUEST
                );
            }

            $this->userService->addToWishlist($user, $productID, $request['collection_id']);

            return self::successJsonMessage(__('cms-catalog::admin.products.add to wishlist'));
        } catch (Throwable $e) {
            Log::error($e->getMessage());
            return self::errorJsonMessage($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @OA\Post (
     *     path="/mobile/products/{id}/remove-from-wishlist",
     *     security={{"Basic": {}}},
     *     tags={"Product"},
     *     summary="Remove a product from an auth user`s wishlist",
     *
     *     @OA\Parameter(name="id", in="path", required=true,
     *         description="Идентификатор товара",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Response(response="200", description="OK",
     *          @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *      ),
     *
     *     @OA\Response(response="401", description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     * @param int $productID
     * @return JsonResponse
     */
    public function removeFromWishlist(int $productID): JsonResponse
    {
        /** @var $user User */
        $user = Auth::user();
        try {
            if(!$this->repo->existBy('id', $productID)){
                throw new InvalidArgumentException(
                    __('cms-catalog::admin.products.exception.not found by id', [
                        "id" => $productID
                    ]),
                    Response::HTTP_BAD_REQUEST
                );
            }

            $this->userService->removeFromWishlist($user, $productID);

            return self::successJsonMessage(__('cms-catalog::admin.products.remove from wishlist'));
        } catch (Throwable $e) {
            Log::error($e->getMessage());

            return self::errorJsonMessage($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @OA\Post (
     *     path="/mobile/products/clear-wishlist",
     *     security={{"Basic": {}}},
     *     tags={"Product"},
     *     summary="Remove all product items from an auth user`s wishlist",
     *
     *     @OA\Response(response="200", description="OK",
     *          @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *      ),
     *
     *     @OA\Response(response="401", description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function clearWishlist(): JsonResponse
    {
        /** @var $user User */
        $user = Auth::user();
        try {
            $this->userService->clearWishlist($user);

            return self::successJsonMessage(__('cms-catalog::admin.products.clear wishlist'));
        } catch (Throwable $e) {
            Log::error($e->getMessage());

            return self::errorJsonMessage($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @OA\Get (
     *     path="/mobile/product-cost-range",
     *     tags={"Product"},
     *     summary="Get a price range products for filter",
     *     @OA\Parameter(name="search", in="query", required=false,
     *         description="Поиск по имени товара",
     *         @OA\Schema(type="string", example="test")
     *     ),
     *
     *     @OA\Parameter(name="collection_id", in="query", required=false,
     *         description="Товары относящиеся к коллекции",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(name="category_id", in="query", required=false,
     *         description="Товары из данной категории",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Response(response="200", description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="Data", type="object",
     *                  @OA\Property(property="max", title="Max", description="Максимальная цена товара", example=13700),
     *                  @OA\Property(property="min", title="Min", description="Минимальная цена товара", example=133)
     *              ),
     *              @OA\Property(property="success", title="Success", example=true),
     *          )
     *      ),
     *
     *     @OA\Response(response="401", description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     * @param Request $request
     * @return JsonResponse
     */
    public function costRange(Request $request): JsonResponse
    {
        $filter = $this->getProductFilter($request);

        try {
            return self::successJsonMessage([
                'max' => $this->repo->getMaxPrice($filter),
                'min' => $this->repo->getMinPrice($filter),
            ]);
        } catch (Throwable $e) {
            Log::error($e->getMessage());

            return self::errorJsonMessage($e->getMessage(), $e->getCode());
        }
    }

    private function getProductFilter(Request $request): array
    {
        $filter = $request->all();

        if (!isset($filter['all']) || !$filter['all']) {
            $filter['active_product'] = true;
            unset($filter['all']);
        }

        if (isset($filter['search'])) {
            $filter['name'] = $filter['search'];
            unset($filter['search']);
        }

        return $filter;
    }
}
