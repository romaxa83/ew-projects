<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\Logger\AALogger;
use App\Http\Controllers\Api\ApiController;
use App\Repositories\Catalog\Service\ServiceRepository;
use App\Resources\Service\ServiceResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ServiceController extends ApiController
{
    public function __construct(protected ServiceRepository $repository)
    {}

    /**
     * @OA\Get(
     *     path="/services",
     *     tags={"Service"},
     *     security={
     *       {"Basic": {}},
     *     },
     *     summary="Display a listing of the services",
     *
     *     @OA\Response(response=200, description="Successful operation", @OA\JsonContent(ref="#/components/schemas/ServiceResource")),
     *
     *     @OA\Response(response="401", description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="400", description="Bad Request", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     *
     * Display a listing of the resource.
     */
    public function list(): JsonResponse|ResourceCollection
    {
        try {
            AALogger::info('Запрос на получение сервисов');

            return ServiceResource::collection($this->repository->getAll(['current', 'parent', 'childs']));
        } catch (\Exception $e){
            AALogger::error($e->getMessage());
            return $this->errorJsonMessage($e->getMessage(), $e->getCode());
        }
    }
}

