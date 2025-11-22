<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\Logger\AALogger;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\V1\ModelBrandUploadRequest;
use App\Services\Catalog\Car\ModelService;
use Illuminate\Http\JsonResponse;

class ModelController extends ApiController
{
    public function __construct(protected ModelService $modelService)
    {}

    /**
     * @OA\Post (
     *     path="models",
     *     tags={"Catalog"},
     *     security={
     *       {"Basic": {}},
     *     },
     *     summary="Upload models and brands",
     *     @OA\Response(
     *         response="200",
     *         description="ok",
     *         @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *                 mediaType="application/json",
     *                 @OA\Schema(
     *                     type="array",
     *                     @OA\Items(ref="#/components/schemas/ModelBrandUploadRequest"),
     *                  )
     *             )
     *     ),
     *     @OA\Response(response="401",description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="400",description="Bad Request", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="500",description="Server Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function store(ModelBrandUploadRequest $request): JsonResponse
    {
        try {
            AALogger::info('UPLOAD OR CREATE Brand and Model');

            $this->modelService->importFromAA($request->all());

            return $this->successJsonMessage([]);
        } catch (\Exception $e){
            AALogger::error($e->getMessage());
            return $this->errorJsonMessage($e->getMessage(), $e->getCode());
        }
    }
}
