<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\ApiController;
use App\Repositories\IosLinkRepository;
use App\Resources\IosLink\IosLinkResource;
use Illuminate\Http\Request;

class IosLinkController extends ApiController
{
    protected $defaultOrderBy = 'status';
    protected $orderBySupport = ['id', 'status', 'code', 'link'];

    public function __construct(protected IosLinkRepository $iosLinkRepository)
    {
        parent::__construct();
    }

    /**
     * @OA\Get (
     *     path="/api/admin/ios-links",
     *     tags = {"IosLink"},
     *     summary="Получение списка ios-links",
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
     *     @OA\Parameter(name="order_by", in="query", required=false,
     *          description="Поле, по которому происходит сортировка",
     *          @OA\Schema(type="string", example="status", default="status", enum={"id", "code", "status", "link"})
     *     ),
     *     @OA\Parameter(name="order_type", in="query", required=false,
     *          description="Тип сортировки",
     *          @OA\Schema(type="string", example="asc", default="desc", enum={"asc", "desc"})
     *     ),
     *     @OA\Parameter(name="status", in="query", required=false,
     *          description="Поле статус - для фильтрации , допустимые значения 1 - активные , 0 - неактивные",
     *          @OA\Schema(type="string", example=1, enum={0, 1})
     *     ),
     *
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(ref="#/components/schemas/IosLinkCollections")),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function index(Request $request)
    {
        try {

            $links = $this->iosLinkRepository->getAllPaginator(
                ['user.profile'],
                $request->all(),
                $this->orderDataForQuery()
            );

            return IosLinkResource::collection($links);
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }

    /**
     * @OA\Get (
     *     path="/api/admin/ios-links-count",
     *     tags = {"IosLink"},
     *     summary="Получение кол-ва ios-links",
     *     security={{"Basic": {}}},
     *
     *     @OA\Parameter(name="status", in="query", required=false,
     *          description="Поле статус - для фильтрации , допустимые значения 1 - активные , 0 - неактивные",
     *          @OA\Schema(type="string", example=1, enum={0, 1})
     *     ),
     *
     *     @OA\Response(response="200", description="OK",
     *         @OA\JsonContent(
     *              @OA\Property(property="data", title="Data", description="Кол-во линков", example=300),
     *              @OA\Property(property="success", title="Success", example=true),
     *         ),
     *     ),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function count(Request $request)
    {
        try {
            return $this->successJsonMessage(
                $this->iosLinkRepository->count($request->only('status'))
            );
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }
}

