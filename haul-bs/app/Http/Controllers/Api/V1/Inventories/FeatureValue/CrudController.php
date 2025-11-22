<?php

namespace App\Http\Controllers\Api\V1\Inventories\FeatureValue;

use App\Http\Controllers\Api\ApiController;
use App\Foundations\Modules\Permission\Permissions as Permission;
use App\Http\Requests\Inventories\FeatureValue\FeatureValueFilterRequest;
use App\Http\Requests\Inventories\FeatureValue\FeatureValueRequest;
use App\Http\Requests\Inventories\FeatureValue\FeatureValueShortListRequest;
use App\Http\Resources\Inventories\FeatureValue\FeatureValuePaginationResource;
use App\Http\Resources\Inventories\FeatureValue\FeatureValueResource;
use App\Http\Resources\Inventories\FeatureValue\FeatureValueShortListResource;
use App\Models\Inventories\Features\Value;
use App\Repositories\Inventories\FeatureValueRepository;
use App\Services\Inventories\FeatureValueService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;

class CrudController extends ApiController
{
    public function __construct(
        protected FeatureValueRepository $repo,
        protected FeatureValueService $service,
    )
    {}

    /**
     * @OA\Get(
     *     path="/api/v1/inventory-feature-values",
     *     tags={"Inventory features"},
     *     security={{"Basic": {}}},
     *     summary="Get inventory feature values pagination list",
     *     operationId="GetInventoryFeatureValuePaginationList",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Parameter(ref="#/components/parameters/Page"),
     *     @OA\Parameter(ref="#/components/parameters/PerPage"),
     *
     *     @OA\Parameter(name="feature_id", in="query", required=false,
     *         description="Filter by feature",
     *         @OA\Schema(type="integer", default="1",)
     *     ),
     *     @OA\Parameter(name="search", in="query", required=false,
     *         description="Scope for filter by name",
     *         @OA\Schema(type="string", default="null",)
     *     ),
     *
     *     @OA\Response(response=200, description="Feature value data",
     *         @OA\JsonContent(ref="#/components/schemas/InventoryFeatureValuePaginationResource")
     *     ),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function index(FeatureValueFilterRequest $request): ResourceCollection
    {
        $this->authorize(Permission\Inventory\FeatureValue\FeatureValueReadPermission::KEY);

        return FeatureValuePaginationResource::collection(
            $this->repo->getAllPagination(
                filters:  $request->validated(),
                sort: ['name' => 'asc']
            )
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/inventory-feature-value/shortlist",
     *     tags={"Inventory features"},
     *     security={{"Basic": {}}},
     *     summary="Get Inventory feature value short list",
     *     operationId="GetInventoryFeatureValuelist",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Parameter(ref="#/components/parameters/ID"),
     *     @OA\Parameter(ref="#/components/parameters/Limit"),
     *
     *     @OA\Parameter(name="feature_id", in="query", required=false,
     *         description="Filter by feature",
     *         @OA\Schema(type="integer", default="1",)
     *     ),
     *     @OA\Parameter(name="search", in="query", description="Scope for filter by name", required=false,
     *          @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(response=200, description="Feature value data",
     *         @OA\JsonContent(ref="#/components/schemas/InventoryFeatureValueShortListResource"),
     *     ),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="422", description="Not Found", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function shortlist(FeatureValueShortListRequest $request): AnonymousResourceCollection
    {
        $this->authorize(Permission\Inventory\FeatureValue\FeatureValueReadPermission::KEY);

        return FeatureValueShortListResource::collection(
            $this->repo->getAll(
                filters: $request->validated(),
                limit: $request->validated('limit') ?? FeatureValueShortListRequest::DEFAULT_LIMIT,
            )
        );
    }

    /**
     * @OA\Post(
     *     path="/api/v1/inventory-feature-values",
     *     tags={"Inventory features"},
     *     security={{"Basic": {}}},
     *     summary="Create inventory feature value",
     *     operationId="CreateInventoryFeatureValue",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(ref="#/components/schemas/FeatureValueRequest")
     *     ),
     *
     *     @OA\Response(response=201, description="Feature value data",
     *         @OA\JsonContent(ref="#/components/schemas/InventoryFeatureValueResource")
     *     ),
     *
     *     @OA\Response(response="422", description="Validation", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function store(FeatureValueRequest $request): FeatureValueResource|JsonResponse
    {
        $this->authorize(Permission\Inventory\FeatureValue\FeatureValueCreatePermission::KEY);

        return FeatureValueResource::make(
            $this->service->create($request->getDto())
        );
    }

    /**
     * @OA\Post(
     *     path="/api/v1/inventory-feture-values/{id}",
     *     tags={"Inventory features"},
     *     security={{"Basic": {}}},
     *     summary="Update inventory feature value",
     *     operationId="UpadteInventoryFeatureValue",
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
     *         @OA\JsonContent(ref="#/components/schemas/FeatureValueRequest")
     *     ),
     *
     *     @OA\Response(response=201, description="Feature value data",
     *         @OA\JsonContent(ref="#/components/schemas/InventoryFeatureValueResource")
     *     ),
     *
     *     @OA\Response(response="422", description="Validation", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="404", description="Not Found", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function update(FeatureValueRequest $request, $id): FeatureValueResource|JsonResponse
    {
        $this->authorize(Permission\Inventory\FeatureValue\FeatureValueUpdatePermission::KEY);

        /** @var $model Value */
        $model = $this->repo->getBy(['id' => $id],
            withException: true,
            exceptionMessage: __("exceptions.inventories.features.value.not_found")
        );

        return FeatureValueResource::make(
            $this->service->update($model, $request->getDto())
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/inventory-feature-values/{id}",
     *     tags={"Inventory features"},
     *     security={{"Basic": {}}},
     *     summary="Get info about inventory feature value",
     *     operationId="GetInfoAboutInventoryFeatureValue",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Parameter(ref="#/components/parameters/IDPath"),
     *
     *     @OA\Response(response=200, description="Feature value data",
     *         @OA\JsonContent(ref="#/components/schemas/InventoryFeatureValueResource")
     *     ),
     *
     *      @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *      @OA\Response(response="404", description="Not Found", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *      @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function show($id): FeatureValueResource
    {
        $this->authorize(Permission\Inventory\FeatureValue\FeatureValueReadPermission::KEY);

        return FeatureValueResource::make(
            $this->repo->getBy(['id' => $id], withException: true,
                exceptionMessage: __("exceptions.inventories.features.value.not_found")
            )
        );
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/inventory-feature-values/{id}",
     *     tags={"Inventory features"},
     *     security={{"Basic": {}}},
     *     summary="Delete inventory feature value",
     *     operationId="DeleteInventoryFeatureValue",
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
        $this->authorize(Permission\Inventory\FeatureValue\FeatureValueDeletePermission::KEY);

        /** @var $model Value */
        $model = $this->repo->getBy(['id' => $id],
            withException: true,
            exceptionMessage: __("exceptions.inventories.features.value.not_found")
        );

        if ($model->hasInventoryRelation()) {
            return $this->errorJsonMessage(
                __("exceptions.inventories.features.value.has_inventory"),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $this->service->delete($model);

        return $this->successJsonMessage(null, Response::HTTP_NO_CONTENT);
    }
}
