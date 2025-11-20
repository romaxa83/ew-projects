<?php

namespace App\Http\Controllers\Api\Site\JD;

use App\Http\Controllers\Api\ApiController;
use App\Repositories\JD\EquipmentGroupRepository;
use App\Resources\Custom\CustomEgResource;
use App\Resources\JD\EquipmentGroupResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EquipmentGroupController extends ApiController
{
    public function __construct(
        protected EquipmentGroupRepository $repo
    )
    {
        parent::__construct();
    }

    /**
     * @OA\Get (
     *     path="/api/equipment-groups",
     *     tags = {"Catalog"},
     *     summary="Получение Equipment Groups",
     *     description ="Получение Equipment Groups John Deere, тянутся и синхронизируются с BOED",
     *     security={{"Basic": {}}},
     *
     *     @OA\Parameter(name="per_page", in="query", required=false,
     *          description="Количество записей на странице",
     *          @OA\Schema(type="integer", example="15", default=10)
     *     ),
     *     @OA\Parameter(name="withoutMD", in="query", required=false,
     *          description="Данные без model description",
     *          @OA\Schema(type="boolean", example=true)
     *     ),
     *     @OA\Parameter(name="forStatistic", in="query", required=false,
     *          description="Формат данных для статистики",
     *          @OA\Schema(type="boolean", example=true)
     *     ),
     *
     *     @OA\Response(response="200", description="Equipment Groups",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="Data", type="array",
     *                  @OA\Items(ref="#/components/schemas/EquipmentGroupResource")
     *              ),
     *              @OA\Property(property="success", title="Success", example=true),
     *         ),
     *     ),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     *
     * todo - тяжелый запрос , нужно закешировать
     */
    public function list(Request $request): JsonResponse
    {
        try {
            if($request['forStatistic'] && filter_var($request['forStatistic'], FILTER_VALIDATE_BOOLEAN)){
                $egs = $this->repo->getByForStatistic();

                return $this->successJsonMessage(\App::make(CustomEgResource::class)->fill($egs));
            }
            if($request['withoutMD'] && filter_var($request['withoutMD'], FILTER_VALIDATE_BOOLEAN)){
                $egs = $this->repo->getAll([], [], $this->orderDataForQuery(),true);

                return $this->successJsonMessage(EquipmentGroupResource::collection($egs));
            }

            $egs = $this->repo->getAll(['modelDescriptions'], [], $this->orderDataForQuery(), true);

            return $this->successJsonMessage(EquipmentGroupResource::collection($egs));
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }
}
