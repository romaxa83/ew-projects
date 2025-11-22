<?php

namespace App\Http\Controllers\Api\V1\Inventories\Feature;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\Inventories\Feature\FeatureECommResource;
use App\Repositories\Inventories\FeatureRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class EComController extends ApiController
{
    public function __construct(
        protected FeatureRepository $repo,
    )
    {}

    /**
     * @OA\Get(
     *     path="/api/v1/e-comm/features",
     *     tags={"E-Commerce"},
     *     security={{"Basic": {}}},
     *     summary="Get inventory features with values list for e-comm",
     *     operationId="GetInventoryFeaturesListForEComm",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization_EComm"),
     *
     *     @OA\Response(response=200, description="Feature data",
     *         @OA\JsonContent(ref="#/components/schemas/FeatureECommResource")
     *     ),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function index(Request $request): ResourceCollection
    {
        return FeatureECommResource::collection(
            $this->repo->getList(
                relation: ['values']
            )
        );
    }
}
