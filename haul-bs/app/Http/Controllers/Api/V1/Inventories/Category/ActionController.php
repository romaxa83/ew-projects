<?php

namespace App\Http\Controllers\Api\V1\Inventories\Category;

use App\Http\Controllers\Api\ApiController;
use App\Foundations\Modules\Permission\Permissions as Permission;
use App\Http\Requests\Inventories\Category\CategoryFilterRequest;
use App\Http\Resources\Inventories\Category\CategoryResource;
use App\Http\Resources\Inventories\Category\CategoryTreeResource;
use App\Repositories\Inventories\CategoryRepository;
use App\Services\Inventories\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ActionController extends ApiController
{
    public function __construct(
        protected CategoryRepository $repo,
        protected CategoryService $service,
    )
    {}

    /**
     * @OA\Get(
     *     path="/api/v1/inventory-categories-tree",
     *     tags={"Inventory categories"},
     *     security={{"Basic": {}}},
     *     summary="Get inventory categories list as tree",
     *     operationId="GetInventoryCategoriesListAsTree",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Parameter(name="search", in="query", required=false,
     *         description="Scope for filter by name",
     *         @OA\Schema(type="string", default="null",)
     *     ),
     *
     *     @OA\Response(response=200, description="Category data",
     *         @OA\JsonContent(ref="#/components/schemas/UnitCategoryListTreeResource")
     *     ),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function listAsTree(CategoryFilterRequest $request): ResourceCollection
    {
        $this->authorize(Permission\Inventory\Category\CategoryReadPermission::KEY);

        if(!empty($request->validated())){
            return CategoryResource::collection(
                $this->repo->getList(
                    filters: $request->validated(),
                    sort: ['position' => 'asc']
                )
            );
        }

        return CategoryTreeResource::collection($this->repo->lisAsTree());
    }

    /**
     * @OA\Get(
     *     path="/api/v1/inventory-categories-tree-select",
     *     tags={"Inventory categories"},
     *     security={{"Basic": {}}},
     *     summary="Get inventory categories list as tree for select",
     *     operationId="GetInventoryCategoriesListAsTreeForSelect",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Response(response=200, description="Category list as tree for select",
     *         @OA\JsonContent(type="object",
     *             @OA\Property(property="data", type="object",
     *                 example={
     *                     "462": "root_1",
     *                     "464": " cat_1_1",
     *                     "467": "  cat_1_1_1",
     *                     "468": "  cat_1_1_2",
     *                     "466": " cat_1_3",
     *                     "465": " cat_1_2",
     *                     "471": "  cat_1_2_3",
     *                     "470": "  cat_1_2_2",
     *                     "469": "  cat_1_2_1"
     *                 },
     *             ),
     *         ),
     *     ),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function listAsTreeForSelect(): JsonResponse
    {
        $this->authorize(Permission\Inventory\Category\CategoryReadPermission::KEY);

        return $this->successJsonData($this->repo->lisAsTreeForSelect());
    }
}

