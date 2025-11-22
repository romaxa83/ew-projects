<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\Logger\AALogger;
use App\Http\Controllers\Api\ApiController;
use App\Repositories\Dealership\DealershipRepository;
use App\Resources\Dealership\DealershipResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;

class DealershipController extends ApiController
{
    public function __construct(protected DealershipRepository $repository)
    {}

    /**
     * @OA\Get(
     *     path="/dealerships",
     *     operationId="examplesAll",
     *     tags={"Dealership"},
     *     security={
     *       {"Basic": {}},
     *     },
     *     summary="Display a listing of the dealership",
     *
     *     @OA\Response(response=200, description="Successful operation", @OA\JsonContent(ref="#/components/schemas/DealershipResource")),
     *
     *     @OA\Response(response="401", description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="400", description="Bad Request", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     *
     * Display a listing of the resource.
     *
     *
     */
    public function list(): JsonResponse|ResourceCollection
    {
        try {
            AALogger::info('Запрос на получение дц');

            return DealershipResource::collection($this->repository->getAll(['current']));
        } catch (\Exception $e){
            AALogger::error($e->getMessage());
            return $this->errorJsonMessage($e->getMessage(), $e->getCode());
        }
    }
}
