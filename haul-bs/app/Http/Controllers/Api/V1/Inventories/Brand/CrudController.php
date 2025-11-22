<?php

namespace App\Http\Controllers\Api\V1\Inventories\Brand;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Inventories\Brand\BrandFilterRequest;
use App\Http\Requests\Inventories\Brand\BrandRequest;
use App\Http\Requests\Inventories\Brand\BrandShortListRequest;
use App\Http\Resources\Inventories\Brand\BrandPaginationResource;
use App\Http\Resources\Inventories\Brand\BrandResource;
use App\Http\Resources\Inventories\Brand\BrandShortListResource;
use App\Foundations\Modules\Permission\Permissions as Permission;
use App\Models\Inventories\Brand;
use App\Repositories\Inventories\BrandRepository;
use App\Services\Inventories\BrandService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;

class CrudController extends ApiController
{
    public function __construct(
        protected BrandRepository $repo,
        protected BrandService $service,
    )
    {}

    /**
     * @OA\Get(
     *     path="/api/v1/inventory-brands",
     *     tags={"Inventory brands"},
     *     security={{"Basic": {}}},
     *     summary="Get inventory brands list",
     *     operationId="GetInventoryBrandsList",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Parameter(ref="#/components/parameters/Page"),
     *     @OA\Parameter(ref="#/components/parameters/PerPage"),
     *
     *     @OA\Parameter(name="search", in="query", required=false,
     *         description="Scope for filter by name",
     *         @OA\Schema(type="string", default="null",)
     *     ),
     *
     *     @OA\Response(response=200, description="Brand data",
     *         @OA\JsonContent(ref="#/components/schemas/InventoryBrandPaginationResource")
     *     ),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function index(BrandFilterRequest $request): ResourceCollection
    {
        $this->authorize(Permission\Inventory\Brand\BrandReadPermission::KEY);

        return BrandPaginationResource::collection(
            $this->repo->getAllPagination(
                filters:  $request->validated(),
                sort: ['name' => 'asc']
            )
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/inventory-brands/shortlist",
     *     tags={"Inventory brands"},
     *     security={{"Basic": {}}},
     *     summary="Get Inventory brand short list",
     *     operationId="GetInventoryBrandlist",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Parameter(ref="#/components/parameters/ID"),
     *     @OA\Parameter(ref="#/components/parameters/Limit"),
     *
     *     @OA\Parameter(name="search", in="query", description="Scope for filter by name", required=false,
     *          @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(response=200, description="Brand data",
     *         @OA\JsonContent(ref="#/components/schemas/InventoryBrandShortListResource"),
     *     ),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="422", description="Not Found", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function shortlist(BrandShortListRequest $request): AnonymousResourceCollection
    {
        $this->authorize(Permission\Inventory\Brand\BrandReadPermission::KEY);

        return BrandShortListResource::collection(
            $this->repo->getAll(
                filters: $request->validated(),
                limit: $request->validated('limit') ?? BrandShortListRequest::DEFAULT_LIMIT,
            )
        );
    }

    /**
     * @OA\Post(
     *     path="/api/v1/inventory-brands",
     *     tags={"Inventory brands"},
     *     security={{"Basic": {}}},
     *     summary="Create inventory brand",
     *     operationId="CreateInventoryBrand",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(ref="#/components/schemas/BrandRequest")
     *     ),
     *
     *     @OA\Response(response=201, description="Brand data",
     *         @OA\JsonContent(ref="#/components/schemas/InventoryBrandResource")
     *     ),
     *
     *     @OA\Response(response="422", description="Validation", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function store(BrandRequest $request): BrandResource|JsonResponse
    {
        $this->authorize(Permission\Inventory\Brand\BrandCreatePermission::KEY);

        return BrandResource::make(
            $this->service->create($request->getDto())
        );
    }

    /**
     * @OA\Post(
     *     path="/api/v1/inventory-brands/{id}",
     *     tags={"Inventory brands"},
     *     security={{"Basic": {}}},
     *     summary="Update inventory brand",
     *     operationId="UpadteInventoryBrand",
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
     *         @OA\JsonContent(ref="#/components/schemas/BrandRequest")
     *     ),
     *
     *     @OA\Response(response=201, description="Brand data",
     *         @OA\JsonContent(ref="#/components/schemas/InventoryBrandResource")
     *     ),
     *
     *     @OA\Response(response="422", description="Validation", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="404", description="Not Found", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function update(BrandRequest $request, $id): BrandResource|JsonResponse
    {
        $this->authorize(Permission\Inventory\Brand\BrandUpdatePermission::KEY);

        /** @var $model Brand*/
        $model = $this->repo->getBy(['id' => $id],
            withException: true,
            exceptionMessage: __("exceptions.inventories.brand.not_found")
        );

        return BrandResource::make(
            $this->service->update($model, $request->getDto())
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/inventory-brands/{id}",
     *     tags={"Inventory brands"},
     *     security={{"Basic": {}}},
     *     summary="Get info about inventory brand",
     *     operationId="GetInfoAboutInventoryBrand",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Parameter(ref="#/components/parameters/IDPath"),
     *
     *     @OA\Response(response=200, description="Brand data",
     *         @OA\JsonContent(ref="#/components/schemas/InventoryBrandResource")
     *     ),
     *
     *      @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *      @OA\Response(response="404", description="Not Found", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *      @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function show($id): BrandResource
    {
        $this->authorize(Permission\Inventory\Brand\BrandReadPermission::KEY);

        return BrandResource::make(
            $this->repo->getBy(['id' => $id], withException: true,
                exceptionMessage: __("exceptions.inventories.brand.not_found")
            )
        );
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/inventory-brands/{id}",
     *     tags={"Inventory brands"},
     *     security={{"Basic": {}}},
     *     summary="Delete inventory brand",
     *     operationId="DeleteInventoryBrand",
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
        $this->authorize(Permission\Inventory\Brand\BrandDeletePermission::KEY);

        /** @var $model Brand */
        $model = $this->repo->getBy(['id' => $id],
            withException: true,
            exceptionMessage: __("exceptions.inventories.brand.not_found")
        );

        if ($model->hasRelatedEntities()) {
            return $this->errorJsonMessage(
                __("exceptions.inventories.brand.has_related_entities"),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $this->service->delete($model);

        return $this->successJsonMessage(null, Response::HTTP_NO_CONTENT);
    }
}
