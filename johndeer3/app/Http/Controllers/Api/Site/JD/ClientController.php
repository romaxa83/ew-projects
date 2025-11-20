<?php

namespace App\Http\Controllers\Api\Site\JD;

use App\Http\Controllers\Api\ApiController;
use App\Repositories\JD\ClientRepository;
use App\Resources\JD\ClientResource;
use Illuminate\Http\Request;

class ClientController extends ApiController
{
    protected $orderBySupport = ['id', 'created_at'];
    protected $defaultOrderBy = 'created_at';

    public function __construct(
        protected ClientRepository $clientRepository
    )
    {
        parent::__construct();
    }

    /**
     * @OA\Get (
     *     path="/api/clients",
     *     tags = {"Catalog"},
     *     summary="Получение клиентов",
     *     description ="Получение клиентов John Deere, тянутся и синхронизируются с BOED",
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
     *     @OA\Parameter(name="search", in="query", required=false,
     *          description="Search (поиск клиента по компании)",
     *          @OA\Schema(type="string", example="макаров")
     *     ),
     *     @OA\Parameter(name="paginator", in="query", required=false,
     *          description="Возврат данных с пагинацией",
     *          @OA\Schema(type="boolean", example=true)
     *     ),
     *     @OA\Parameter(name="order_by", in="query", required=false,
     *          description="Поле, по которому происходит сортировка",
     *          @OA\Schema(type="string", example="id", default="created_at", enum={"id", "created_at"})
     *     ),
     *     @OA\Parameter(name="order_type", in="query", required=false,
     *          description="Тип сортировки",
     *          @OA\Schema(type="string", example="asc", default="desc", enum={"asc", "desc"})
     *     ),
     *
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(ref="#/components/schemas/ClientCollections")),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function list(Request $request)
    {
        try {
            return ClientResource::collection(
                $this->clientRepository->getAllWrap(
                    ['region'],
                    $request->all(),
                    [],
                true
                )
            );
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }
}
