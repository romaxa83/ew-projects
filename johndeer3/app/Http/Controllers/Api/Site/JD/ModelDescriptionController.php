<?php

namespace App\Http\Controllers\Api\Site\JD;

use App\Http\Controllers\Api\ApiController;
use App\Http\Request\Catalog\JD\MdListRequest;
use App\Repositories\JD\ProductRepository;
use App\Repositories\JD\ModelDescriptionRepository;
use App\Resources\JD\ModelDescriptionResource;
use App\Type\ModelDescription;
use Illuminate\Http\JsonResponse;

class ModelDescriptionController extends ApiController
{
    public function __construct(
        protected ModelDescriptionRepository $repo,
        protected ProductRepository $productRepository
    )
    {
        parent::__construct();
    }

    /**
     * @OA\Get (
     *     path="/api/model-descriptions",
     *     tags = {"Catalog"},
     *     summary="Получение Model Descriptions",
     *     description ="Получение Model Descriptions John Deere, тянутся и синхронизируются с BOED",
     *     security={{"Basic": {}}},
     *
     *     @OA\Parameter(name="eg_id", in="query", required=false,
     *          description="ID equipment group",
     *          @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(name="forStatistic", in="query", required=false,
     *          description="Формат данных для статистики",
     *          @OA\Schema(type="boolean", example=true)
     *     ),
     *     @OA\Parameter(name="only_exist_report", in="query", required=false,
     *          description="Возвращать только те model description который участвуют в отчетах",
     *          @OA\Schema(type="boolean", example=true)
     *     ),
     *
     *     @OA\Response(response="200", description="ModelDescription",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="Data", type="array",
     *                  @OA\Items(ref="#/components/schemas/ModelDescriptionResource")
     *              ),
     *              @OA\Property(property="success", title="Success", example=true),
     *         ),
     *     ),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function list(MdListRequest $request): JsonResponse
    {
        try {
            $models = $this->repo->getAll(
                ['product.sizeParameter'],
                $request->all(),
                $this->orderDataForQuery(),
                true
            );

            return $this->successJsonMessage(ModelDescriptionResource::collection($models));
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }

    /**
     * @OA\Get (
     *     path="/api/model-descriptions/types",
     *     tags = {"Catalog"},
     *     summary="Получение типов model descriptions (для фильтра)",
     *     description ="Получение Model Descriptions John Deere, тянутся и синхронизируются с BOED",
     *     security={{"Basic": {}}},
     *
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(ref="#/components/schemas/SuccessWithSimpleData")),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function types(): JsonResponse
    {
        return $this->successJsonMessage(ModelDescription::list());
    }

    /**
     * @OA\Get (
     *     path="/api/model-descriptions/sizes",
     *     tags = {"Catalog"},
     *     summary="Получение размеров modelDescriptions (для фильтра)",
     *     security={{"Basic": {}}},
     *
     *     @OA\Response(response="200", description="Success", @OA\JsonContent(ref="#/components/schemas/SuccessWithSimpleData")),
     *     @OA\Response(response="400", description="Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function sizes(): JsonResponse
    {
        try {
            return $this->successJsonMessage(
                $this->productRepository->getSizeForSelect()
            );
        } catch (\Exception $error){
            return $this->errorJsonMessage($error->getMessage());
        }
    }
}
