<?php

namespace App\Http\Controllers\Api\V1\Inventories\Inventory;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\Inventories\Inventory\InventoryECommResource;
use App\Repositories\Inventories\InventoryRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class EComController extends ApiController
{
    public function __construct(
        protected InventoryRepository $repo,
    )
    {}

    /**
     * @OA\Get(
     *     path="/api/v1/e-comm/inventories",
     *     tags={"E-Commerce"},
     *     security={{"Basic": {}}},
     *     summary="Get inventory list for e-comm",
     *     operationId="GetInventoryInventoriesListForEComm",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization_EComm"),
     *
     *     @OA\Response(response=200, description="Inventory data",
     *         @OA\JsonContent(ref="#/components/schemas/InventoryECommResource")
     *     ),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function index(Request $request): ResourceCollection
    {
        return InventoryECommResource::collection(
            $this->repo->getList(
                relation: ['unit', 'media']
            )
        );
    }
}
