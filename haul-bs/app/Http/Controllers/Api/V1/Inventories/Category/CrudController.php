<?php

namespace App\Http\Controllers\Api\V1\Inventories\Category;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Inventories\Category\CategoryFilterRequest;
use App\Http\Requests\Inventories\Category\CategoryRequest;
use App\Http\Resources\Inventories\Category\CategoryResource;
use App\Foundations\Modules\Permission\Permissions as Permission;
use App\Models\Inventories\Category;
use App\Repositories\Inventories\CategoryRepository;
use App\Services\Inventories\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;

class CrudController extends ApiController
{
    public function __construct(
        protected CategoryRepository $repo,
        protected CategoryService $service,
    )
    {}

    /**
     * @OA\Get(
     *     path="/api/v1/inventory-categories",
     *     tags={"Inventory categories"},
     *     security={{"Basic": {}}},
     *     summary="Get inventory categories list",
     *     operationId="GetInventoryCategoriesList",
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
     *         @OA\JsonContent(ref="#/components/schemas/UnitCategoryListResource")
     *     ),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function index(CategoryFilterRequest $request): ResourceCollection
    {
        $this->authorize(Permission\Inventory\Category\CategoryReadPermission::KEY);

        return CategoryResource::collection(
            $this->repo->getList(
                filters: $request->validated()
            )
        );
    }

    /**
     * @OA\Post(
     *     path="/api/v1/inventory-categories",
     *     tags={"Inventory categories"},
     *     security={{"Basic": {}}},
     *     summary="Create inventory category",
     *     operationId="CreateInventoryCategory",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CategoryRequest")
     *     ),
     *
     *     @OA\Response(response=201, description="Category data",
     *         @OA\JsonContent(ref="#/components/schemas/UnitCategoryResource")
     *     ),
     *
     *     @OA\Response(response="422", description="Validation", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function store(CategoryRequest $request): CategoryResource|JsonResponse
    {
        $this->authorize(Permission\Inventory\Category\CategoryCreatePermission::KEY);

        return CategoryResource::make(
            $this->service->create($request->getDto())
        );
    }

    /**
     * @OA\Post(
     *     path="/api/v1/inventory-categories/{id}",
     *     tags={"Inventory categories"},
     *     security={{"Basic": {}}},
     *     summary="Update inventory category",
     *     operationId="UpadteInventoryCategory",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *
     *     @OA\Parameter(ref="#/components/parameters/IDPath"),
     *
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(ref="#/components/schemas/CategoryRequest")
     *     ),
     *
     *     @OA\Response(response=201, description="Category data",
     *         @OA\JsonContent(ref="#/components/schemas/UnitCategoryResource")
     *     ),
     *
     *     @OA\Response(response="422", description="Validation", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="404", description="Not Found", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function update(CategoryRequest $request, $id): CategoryResource|JsonResponse
    {
        $this->authorize(Permission\Inventory\Category\CategoryUpdatePermission::KEY);

        /** @var $model Category */
        $model = $this->repo->getById($id);

        return CategoryResource::make(
            $this->service->update($model, $request->getDto())
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/inventory-categories/{id}",
     *     tags={"Inventory categories"},
     *     security={{"Basic": {}}},
     *     summary="Get info about inventory category",
     *     operationId="GetInfoAboutInventoryCategory",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Parameter(ref="#/components/parameters/IDPath"),
     *
     *     @OA\Response(response=200, description="Category data",
     *         @OA\JsonContent(ref="#/components/schemas/UnitCategoryResource")
     *     ),
     *
     *      @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *      @OA\Response(response="404", description="Not Found", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *      @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function show($id): CategoryResource
    {
        $this->authorize(Permission\Inventory\Category\CategoryReadPermission::KEY);

        return CategoryResource::make(
            $this->repo->getById($id)
        );
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/inventory-categories/{id}",
     *     tags={"Inventory categories"},
     *     security={{"Basic": {}}},
     *     summary="Delete inventory category",
     *     operationId="DeleteInventoryCategory",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/IDPath"),
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Response(response=204, description="Successful delete"),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="404", description="Not Found", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function delete($id): JsonResponse
    {
        $this->authorize(Permission\Inventory\Category\CategoryDeletePermission::KEY);

        /** @var $model Category */
        $model = $this->repo->getById($id);

        if ($model->hasRelatedEntities()) {
            $link = str_replace('{id}', $model->id, config('routes.front.inventories_with_category_filter_url'));

            return $this->errorJsonMessage(
                __("exceptions.inventories.category.has_inventories", ['link' => $link]),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $this->service->delete($model);

        return $this->successJsonMessage(null, Response::HTTP_NO_CONTENT);
    }
}
