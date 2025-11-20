<?php

namespace App\Http\Controllers\Api\Site\JD;

use App\Http\Controllers\Api\ApiController;
use App\Repositories\JD\DealersRepository;
use App\Resources\Custom\CustomDealerResource;
use Illuminate\Http\Request;
use App\Resources\JD\DealerResource;

class DealerController extends ApiController
{
    public function __construct(
        protected DealersRepository $repo
    )
    {
        parent::__construct();
    }

    /**
     * @OA\Get (
     *     path="/api/dealers",
     *     tags = {"Catalog"},
     *     summary="Получение дилеров",
     *     description ="Получение дилеров John Deere, тянутся и синхронизируются с BOED",
     *     security={{"Basic": {}}},
     *
     *     @OA\Parameter(name="page", in="query", required=false,
     *          description="Страница пагинации",
     *          @OA\Schema(type="integer", example="5")
     *     ),
     *     @OA\Parameter(name="perPage", in="query", required=false,
     *          description="Количество записей на странице",
     *          @OA\Schema(type="integer", example="15", default=10)
     *     ),
     *     @OA\Parameter(name="name", in="query", required=false,
     *          description="Поиск по имени",
     *          @OA\Schema(type="string", example="Agris")
     *     ),
     *     @OA\Parameter(name="country_id", in="query", required=false,
     *          description="фильтр по стране(национальность)",
     *          @OA\Schema(type="integer", example=2)
     *     ),
     *     @OA\Parameter(name="forStatistic", in="query", required=false,
     *          description="Формат данных для статистики",
     *          @OA\Schema(type="boolean", example=true)
     *     ),
     *
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(ref="#/components/schemas/DealerCollections")),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function list(Request $request)
    {
        try {
            $dealers = $this->repo->getAllActive($request->all());

            if(isset($request['forStatistic']) && filter_var($request['forStatistic'], FILTER_VALIDATE_BOOLEAN)){
                return $this->successJsonMessage(\App::make(CustomDealerResource::class)
                    ->forStatistics()
                    ->fill($dealers));
            }

            return DealerResource::collection($dealers);
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }
}
