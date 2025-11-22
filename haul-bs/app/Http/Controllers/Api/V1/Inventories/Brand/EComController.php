<?php

namespace App\Http\Controllers\Api\V1\Inventories\Brand;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\Inventories\Brand\BrandECommResource;
use App\Repositories\Inventories\BrandRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class EComController extends ApiController
{
    public function __construct(
        protected BrandRepository $repo,
    )
    {}

    /**
     * @OA\Get(
     *     path="/api/v1/e-comm/brands",
     *     tags={"E-Commerce"},
     *     security={{"Basic": {}}},
     *     summary="Get inventory brands list for e-comm",
     *     operationId="GetInventoryBrandsListForEComm",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization_EComm"),
     *
     *     @OA\Response(response=200, description="Brand data",
     *         @OA\JsonContent(ref="#/components/schemas/BrandECommResource")
     *     ),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function index(Request $request): ResourceCollection
    {
        return BrandECommResource::collection(
            $this->repo->getList()
        );
    }
}
