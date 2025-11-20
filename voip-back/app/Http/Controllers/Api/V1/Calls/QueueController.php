<?php

namespace App\Http\Controllers\Api\V1\Calls;

use App\Enums\Calls\QueueStatus;
use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\Calls\QueueResource;
use App\Repositories\Calls\QueueRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;

class QueueController extends ApiController
{
    public function __construct(
        protected QueueRepository $repo,
    )
    {}

    /**
     * @OA\Get(
     *     path="/calls/queues",
     *     tags={"Calls"},
     *     security={
     *       {"Basic": {}},
     *     },
     *     summary="A list of call queues",
     *
     *     @OA\Response(response="200", description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="Data", type="array",
     *                  @OA\Items(ref="#/components/schemas/QueueResource")
     *              ),
     *              @OA\Property(property="success", title="Success", example=true),
     *         ),
     *     ),
     *
     *     @OA\Response(response="401", description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="400", description="Bad Request", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function list(): JsonResponse|ResourceCollection
    {
        try {
            return $this->successJsonMessage(
                QueueResource::collection($this->repo->getAllByFields([
                    'status' => QueueStatus::forApi(),
                ],[
                    'department',
                    'employee',
                ]))
            );
        } catch (\Throwable $e){
            return $this->errorJsonMessage($e->getMessage(), $e->getCode());
        }
    }
}
