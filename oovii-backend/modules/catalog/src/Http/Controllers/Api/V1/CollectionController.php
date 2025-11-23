<?php

namespace WezomCms\Catalog\Http\Controllers\Api\V1;

use App;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;
use Throwable;
use WezomCms\Catalog\Http\Requests\Api\V1\CollectionsRequest;
use WezomCms\Catalog\Http\Resources\V1\Collections\CollectionResource;
use WezomCms\Catalog\Http\Resources\V1\Collections\CollectionSimpleResource;
use WezomCms\Catalog\Models\Collections\Collection;
use WezomCms\Catalog\Repositories\CollectionCategoryRepository;
use WezomCms\Catalog\Repositories\CollectionRepository;
use WezomCms\Core\Http\Controllers\ApiController;
use WezomCms\Core\Models\Setting;
use WezomCms\Core\Traits\GetModelFilterWithProductsTrait;

class CollectionController extends ApiController
{
    use GetModelFilterWithProductsTrait;

    protected array $orderBySupport = ['id', 'type', 'start_at', 'end_at', 'created_at'];

    public function __construct(
        protected CollectionCategoryRepository $repoCategory,
        protected CollectionRepository $repo,
    ) {
        parent::__construct();
    }

    /**
     * @OA\Get (
     *     path="/mobile/collections",
     *     tags={"Collections"},
     *     summary="Get list collections",
     *     @OA\Parameter(name="order_by", in="query", required=false,
     *         description="По какому полю сортировать, поддерживается - [id, type, start_at, end_at, created_at], если нужно передать несколько полей то так - ?order_by[]=id&order_by[]=sort",
     *         @OA\Schema(type="string",example="id")
     *     ),
     *     @OA\Parameter(name="order_type", in="query", required=false,
     *         description="Тип сортировки, поддерживается - [asc, desc],по умолчанию используеться desc, если передается несколько полей для сортировки, то для каждого, последовательно, можно передовать тип - ?order_type[]=asc&order_type[]=desc",
     *         @OA\Schema(type="string",example="asc")
     *     ),
     *
     *     @OA\Response(response="200", description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="data", type="array",
     *                  @OA\Items(ref="#/components/schemas/CollectionSimpleResource")
     *              ),
     *              @OA\Property(property="success", title="Success", example=true),
     *              @OA\Property(property="code", title="Code", example=0),
     *          )
     *     ),
     *     @OA\Response(response="401", description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     * @param CollectionsRequest $request
     * @return JsonResponse
     */
    public function list(CollectionsRequest $request): JsonResponse
    {
        try {
            return self::successJsonMessage(
                CollectionSimpleResource::collection(
                    $this->repo->getAllForFront(
                        [],
                        $this->getFilters($request),
                        $this->orderDataForQuery()
                    ),
                )
            );
        } catch (Throwable $e) {
            Log::error($e->getMessage());
            return self::errorJsonMessage($e->getMessage());
        }
    }

    /**
     * @OA\Get (
     *     path="/mobile/collections/{id}",
     *     tags={"Collections"},
     *     summary="Get list collections",
     *
     *     @OA\Parameter(name="id", in="path", required=true,
     *         description="Идентификатор коллекции",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Response(response="200", description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="data", type="object",
     *                  ref="#/components/schemas/CollectionResource"
     *              ),
     *              @OA\Property(property="success", title="Success", example=true),
     *              @OA\Property(property="code", title="Code", example=0),
     *          )
     *     ),
     *     @OA\Response(response="401", description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     * @param $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        try {
            return self::successJsonMessage(
                CollectionResource::make(
                    $this->repo->findOneBy(
                        'id',
                        $id,
                        ['availableProducts']
                    ),
                )
            );
        } catch (Throwable $e) {
            Log::error($e->getMessage());
            return self::errorJsonMessage($e->getMessage());
        }
    }

    /**
     * @OA\Get (
     *     path="/mobile/collections-all_products",
     *     tags={"Collections"},
     *     summary="Get data for block 'All products'",
     *
     *     @OA\Response(response="200", description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="data", type="object",
     *                  @OA\Property(property="name", title="Name", description="Название коллекции", example="All Product"),
     *                  @OA\Property(property="image", title="Image", description="Ссылка на картинку", example="http://192.168.175.1/storage/collections/medium/lWRZqMXUX0cnKagh73IWyP1exMcasD7Mz5sEHaed.png?v=1643982687"),
     *              ),
     *              @OA\Property(property="success", title="Success", example=true),
     *              @OA\Property(property="code", title="Code", example=0),
     *          )
     *     ),
     *     @OA\Response(response="401", description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function allProducts(): JsonResponse
    {
        try {
            $tmp = [
                'name' => null,
                'image' => null
            ];

            $data = Setting::query()->where(
                [
                    ['module', 'collection'],
                    ['group', 'page']
                ]
            )->get();

            foreach ($data as $item) {
                if ($item->type === 'image' && $item->key === App::getLocale()) {
                    $tmp['image'] = $item->getImageUrl();
                }
                if ($item->key === 'title') {
                    $tmp['name'] = $item->value;
                }
            }

            return self::successJsonMessage([$tmp]);
        } catch (Throwable $e) {
            Log::error($e->getMessage());
            return self::errorJsonMessage($e->getMessage());
        }
    }

    /**
     * @OA\Post (
     *     path="/mobile/collections/check-hashes",
     *     tags={"Collections"},
     *     summary="Check collections data hash",
     *
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                  @OA\Property(property="3", title="3", description="Ключ - id коллекции, значение - ее hashData"),
     *                  example={"3": "4ab10db123acf9f34f4b78b04fde8028", "6": "07900e600cd6b255e7fd5f7d0ef234ea"}
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response="200", description="OK",
     *           @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                  @OA\Property(property="data", title="Data", description="", type="object",
     *                      @OA\Property(property="hashMap", title="Hash map", description="Данные по хешам", type="object",
     *                          @OA\Property(property="3", title="3", description="Ключ - id коллекции, значение - ее hashData"),
     *                         ),
     *                         @OA\Property(property="reload", title="Reload", description="Перезагружать коллекцию или нет (будет true - если хотя бь один хеш не совпал)"),
     *                  ),
     *                  example={"data": {"hashMap": {"3": "true", "6": "false"}, "reload": true}},
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response="401", description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     * @param Request
     * @return JsonResponse
     */
    public function checkHash(Request $request): JsonResponse
    {
        $tmp = [];
        $data = $request->all();
        $ids = array_keys($data);
        try {
            $models = $this->repo->getAllByFieldIn('id', $ids);
            foreach ($models as $model){
                /** @var $model Collection */
                $tmp['hashMap'][$model->id] = $model->equalsHash($data[$model->id]);
            }
            $tmp['reload'] = false;
            if(in_array(false, $tmp['hashMap'])){
                $tmp['reload'] = true;
            }

            return self::successJsonMessage($tmp);
        } catch (Throwable $e) {
            Log::error($e->getMessage());
            return self::errorJsonMessage($e->getMessage());
        }
    }
}
