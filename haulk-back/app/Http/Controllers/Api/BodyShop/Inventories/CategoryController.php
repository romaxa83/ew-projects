<?php

namespace App\Http\Controllers\Api\BodyShop\Inventories;

use App\Exceptions\HasRelatedEntitiesException;
use App\Http\Controllers\ApiController;
use App\Http\Requests\BodyShop\Inventories\CategoryIndexRequest;
use App\Http\Requests\BodyShop\Inventories\CategoryRequest;
use App\Http\Resources\BodyShop\Inventories\CategoryResource;
use App\Models\BodyShop\Inventories\Category;
use App\Services\BodyShop\Inventories\CategoryService;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Throwable;

class CategoryController extends ApiController
{
    protected CategoryService $service;

    public function __construct(CategoryService $service)
    {
        parent::__construct();

        $this->service = $service;
    }

    /**
     * @param CategoryIndexRequest $request
     * @return AnonymousResourceCollection
     *
     * @OA\Get(
     *     path="/api/body-shop/inventory-categories",
     *     tags={"Inventory Categories"},
     *     summary="Get inventory categories list",
     *     operationId="Get Inventory categories data",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(
     *          name="q",
     *          in="query",
     *          description="Scope for filter by name, email, phone",
     *          required=false,
     *          @OA\Schema(
     *              type="string",
     *              default="name",
     *          )
     *     ),
     *     @OA\Parameter(name="order_by", in="query", description="Field for sort", required=false,
     *          @OA\Schema(type="string", default="status", enum ={"name"})
     *     ),
     *     @OA\Parameter(name="order_type", in="query", description="Type for sort", required=false,
     *          @OA\Schema(type="string", default="desc", enum ={"asc","desc"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/InventoryCategoryList"),
     *     )
     * )
     */
    public function index(CategoryIndexRequest $request): AnonymousResourceCollection
    {
        $this->authorize('inventory-categories');

        $categories = Category::query()
            ->filter($request->validated())
            ->orderBy($request->order_by, $request->order_type)
            ->get();

        return CategoryResource::collection($categories);
    }

    /**
     * @param CategoryRequest $request
     * @return CategoryResource|JsonResponse
     * @throws AuthorizationException
     * @throws Throwable
     * @OA\Post(path="/api/body-shop/inventory-categories",
     *     tags={"Inventory Categories"},
     *     summary="Create Inventory Category",
     *     operationId="Create Inventory Category",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="name", in="query", description="Inventory Category name", required=true,
     *          @OA\Schema(type="string", default="Category 1",)
     *     ),
     *     @OA\Response(response=201, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/InventoryCategory")
     *     ),
     * )
     */
    public function store(CategoryRequest $request)
    {
        $this->authorize('inventory-categories create');

        try {
            $category = $this->service->create($request->validated());

            return CategoryResource::make($category);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->makeErrorResponse($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     *
     * @OA\Get(
     *     path="/api/body-shop/inventory-categories/{categoryId}",
     *     tags={"Inventory Categories"},
     *     summary="Get inventory category record",
     *     operationId="Get inventory category record",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="Inventory Category id",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/InventoryCategory")
     *     ),
     * )
     * @param Category $category
     * @return CategoryResource
     * @throws AuthorizationException
     */
    public function show(Category $inventoryCategory): CategoryResource
    {
        $this->authorize('inventory-categories read');

        return CategoryResource::make($inventoryCategory);
    }

    /**
     * Update the specified resource in storage.
     *
     * @OA\Put(
     *     path="/api/body-shop/inventory-categories/{categoryId}",
     *     tags={"Inventory Categories"},
     *     summary="Update inventory category record",
     *     operationId="Update invettory category",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="id", in="path", description="Inventory Category id", required=true,
     *          @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(name="name", in="query", description="Inventory Category name", required=true,
     *          @OA\Schema(type="string", default="Category 1",)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/InventoryCategory")
     *     ),
     * )
     * @param CategoryRequest $request
     * @param Category $inventoryCategory
     * @return CategoryResource|JsonResponse
     * @throws AuthorizationException
     * @throws Throwable
     */
    public function update(CategoryRequest $request, Category $inventoryCategory)
    {
        $this->authorize('inventory-categories update');

        try {
            $this->service->update($inventoryCategory, $request->validated());

            return CategoryResource::make($inventoryCategory);
        } catch (Exception $e) {
            Log::error($e);

            return $this->makeErrorResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @OA\Delete(
     *     path="/api/body-shop/inventory-categories/{categoryId}",
     *     tags={"Inventory Categories"},
     *     summary="Delete inventory category",
     *     operationId="Delete inventory category",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="Inventory Category id",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *              default="1",
     *          )
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Successful operation",
     *     ),
     * )
     * @param Category $inventoryCategory
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function destroy(Category $inventoryCategory): JsonResponse
    {
        $this->authorize('inventory-categories delete');

        try {
            $this->service->destroy($inventoryCategory);

            return $this->makeSuccessResponse(null, Response::HTTP_NO_CONTENT);
        } catch (HasRelatedEntitiesException $exception) {
            return $this->makeErrorResponse(
                trans(
                    'This category is used for some inventory. Please check the list of inventory with this category. <a href=":link">Check Inventory</a>',
                    [
                        'link' => str_replace('{id}', $inventoryCategory->id, config('frontend.bs_inventories_with_category_filter_url'))
                    ]
                ),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        } catch (Exception $e) {
            Log::error($e);

            return $this->makeErrorResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
