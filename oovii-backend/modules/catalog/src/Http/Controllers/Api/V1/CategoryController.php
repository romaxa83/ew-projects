<?php

namespace WezomCms\Catalog\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;
use Throwable;
use WezomCms\Catalog\Http\Resources\V1\CategoryResource;
use WezomCms\Catalog\Repositories\CategoryRepository;
use WezomCms\Core\Http\Controllers\ApiController;

class CategoryController extends ApiController
{
    protected array $orderBySupport = ['id', 'sort'];

    public function __construct(
        protected CategoryRepository $repo,
    )
    {
        parent::__construct();
    }

    /**
     * @OA\Get (
     *     path="/mobile/categories",
     *     tags={"Catalog"},
     *     summary="Get a list of product categories",
     *     @OA\Parameter(name="collection_id", in="query", required=false,
     *         description="Получение тех категорий, которые присвоены товарам в данной коллекции",
     *         @OA\Schema(type="integer",example=1)
     *     ),
     *     @OA\Parameter(name="order_by", in="query", required=false,
     *         description="По какому полю сортировать, поддерживается - [id, sort], если нужно передать несколько полей то так - ?order_by[]=id&order_by[]=sort",
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
     *                  ref="#/components/schemas/CategoryResource"
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
                $request->all(),
                true,
                $this->orderDataForQuery()
            );

            return self::successJsonMessage(CategoryResource::collection($models));
        } catch (Throwable $e) {
            Log::error($e->getMessage());
            return self::errorJsonMessage($e->getMessage());
        }
    }
}

