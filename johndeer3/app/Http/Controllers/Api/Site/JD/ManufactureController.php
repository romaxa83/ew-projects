<?php

namespace App\Http\Controllers\Api\Site\JD;

use App\Http\Controllers\Api\ApiController;
use App\Repositories\JD\ManufacturerRepository;
use App\Resources\JD\ManufacturerResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ManufactureController extends ApiController
{
    protected $orderBySupport = ['id', 'position'];
    protected $defaultOrderBy = 'position';

    private $manufacturerRepository;

    public function __construct(
        ManufacturerRepository $manufacturerRepository
    )
    {
        $this->manufacturerRepository = $manufacturerRepository;

        parent::__construct();
    }

    /**
     * @OA\Get (
     *     path="/api/manufacturers",
     *     tags = {"Catalog"},
     *     summary="Получение производителей",
     *     description ="Получение производителей John Deere, тянутся и синхронизируются с BOED",
     *     security={{"Basic": {}}},
     *
     *     @OA\Parameter(name="page", in="query", required=false,
     *          description="Страница пагинации",
     *          @OA\Schema(type="integer", example="5")
     *     ),
     *     @OA\Parameter(name="per_page", in="query", required=false,
     *          description="Количество записей на странице",
     *          @OA\Schema(type="integer", example="15", default=10)
     *     ),
     *     @OA\Parameter(name="paginator", in="query", required=false,
     *          description="Возврат данных с пагинацией",
     *          @OA\Schema(type="boolean", example=true, default=false)
     *     ),
     *     @OA\Parameter(name="order_by", in="query", required=false,
     *          description="Поле, по которому происходит сортировка",
     *          @OA\Schema(type="string", example="id", default="position", enum={"id", "position"})
     *     ),
     *     @OA\Parameter(name="order_type", in="query", required=false,
     *          description="Тип сортировки",
     *          @OA\Schema(type="string", example="asc", default="desc", enum={"asc", "desc"})
     *     ),
     *
     *     @OA\Response(response="200", description="Manufacturer",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="Data", type="array",
     *                  @OA\Items(ref="#/components/schemas/ManufacturerResource")
     *              ),
     *              @OA\Property(property="success", title="Success", example=true),
     *         ),
     *     ),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function list(Request $request): JsonResponse
    {
        try {
            $filter = $request->all();
            $filter['paginator'] = $request['paginator'] ?? false;

            $models = $this->manufacturerRepository->getAllWrap(
                [],
                $filter,
                $this->orderDataForQuery(),
                true
            );

            return $this->successJsonMessage(ManufacturerResource::collection($models));
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }
}
