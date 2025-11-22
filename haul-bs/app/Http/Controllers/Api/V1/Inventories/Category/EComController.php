<?php

namespace App\Http\Controllers\Api\V1\Inventories\Category;

use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\Inventories\Category\CategoryECommResource;
use App\Repositories\Inventories\CategoryRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class EComController extends ApiController
{
    public function __construct(
        protected CategoryRepository $repo,
    )
    {}

    /**
     * @OA\Get(
     *     path="/api/v1/e-comm/categories",
     *     tags={"E-Commerce"},
     *     security={{"Basic": {}}},
     *     summary="Get inventory categories list for e-comm",
     *     operationId="GetInventoryCategoriesListForEComm",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization_EComm"),
     *
     *     @OA\Response(response=200, description="Category data",
     *         @OA\JsonContent(ref="#/components/schemas/CategoryECommResource")
     *     ),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function index(Request $request): ResourceCollection
    {
        return CategoryECommResource::collection(
            $this->repo->getList()
        );
    }
}
