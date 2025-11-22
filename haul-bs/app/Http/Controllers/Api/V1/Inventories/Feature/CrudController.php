<?php

namespace App\Http\Controllers\Api\V1\Inventories\Feature;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Inventories\Feature\FeatureFilterRequest;
use App\Http\Requests\Inventories\Feature\FeatureRequest;
use App\Http\Requests\Inventories\Feature\FeatureShortListRequest;
use App\Foundations\Modules\Permission\Permissions as Permission;
use App\Http\Resources\Inventories\Feature\FeaturePaginationResource;
use App\Http\Resources\Inventories\Feature\FeatureResource;
use App\Http\Resources\Inventories\Feature\FeatureShortListResource;
use App\Models\Inventories\Features\Feature;
use App\Repositories\Inventories\FeatureRepository;
use App\Services\Inventories\FeatureService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;

class CrudController extends ApiController
{
    public function __construct(
        protected FeatureRepository $repo,
        protected FeatureService $service,
    )
    {}

    /**
     * @OA\Get(
     *     path="/api/v1/inventory-features",
     *     tags={"Inventory features"},
     *     security={{"Basic": {}}},
     *     summary="Get inventory feature pagination list",
     *     operationId="GetInventoryFeaturePaginationList",
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
     *     @OA\Response(response=200, description="Feature data",
     *         @OA\JsonContent(ref="#/components/schemas/InventoryFeaturePaginationResource")
     *     ),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function index(FeatureFilterRequest $request): ResourceCollection
    {
        $this->authorize(Permission\Inventory\Feature\FeatureReadPermission::KEY);

        return FeaturePaginationResource::collection(
            $this->repo->getAllPagination(
                filters:  $request->validated(),
                sort: ['name' => 'asc']
            )
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/inventory-features/shortlist",
     *     tags={"Inventory features"},
     *     security={{"Basic": {}}},
     *     summary="Get Inventory feature short list",
     *     operationId="GetInventoryFeaturelist",
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
     *     @OA\Response(response=200, description="Feature data",
     *         @OA\JsonContent(ref="#/components/schemas/InventoryFeatureShortListResource"),
     *     ),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="422", description="Not Found", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function shortlist(FeatureShortListRequest $request): AnonymousResourceCollection
    {
        $this->authorize(Permission\Inventory\Feature\FeatureReadPermission::KEY);

        return FeatureShortListResource::collection(
            $this->repo->getAll(
                filters: $request->validated(),
                limit: $request->validated('limit') ?? FeatureShortListRequest::DEFAULT_LIMIT,
            )
        );
    }

    /**
     * @OA\Post(
     *     path="/api/v1/inventory-features",
     *     tags={"Inventory features"},
     *     security={{"Basic": {}}},
     *     summary="Create inventory feature",
     *     operationId="CreateInventoryFeature",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(ref="#/components/schemas/FeatureRequest")
     *     ),
     *
     *     @OA\Response(response=201, description="Feature data",
     *         @OA\JsonContent(ref="#/components/schemas/InventoryFeatureResource")
     *     ),
     *
     *     @OA\Response(response="422", description="Validation", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function store(FeatureRequest $request): FeatureResource|JsonResponse
    {
        $this->authorize(Permission\Inventory\Feature\FeatureCreatePermission::KEY);

        return FeatureResource::make(
            $this->service->create($request->getDto())
        );
    }

    /**
     * @OA\Post(
     *     path="/api/v1/inventory-fetures/{id}",
     *     tags={"Inventory features"},
     *     security={{"Basic": {}}},
     *     summary="Update inventory feature",
     *     operationId="UpadteInventoryFeature",
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
     *         @OA\JsonContent(ref="#/components/schemas/FeatureRequest")
     *     ),
     *
     *     @OA\Response(response=201, description="Feature data",
     *         @OA\JsonContent(ref="#/components/schemas/InventoryFeatureResource")
     *     ),
     *
     *     @OA\Response(response="422", description="Validation", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="404", description="Not Found", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function update(FeatureRequest $request, $id): FeatureResource|JsonResponse
    {
        $this->authorize(Permission\Inventory\Feature\FeatureUpdatePermission::KEY);

        /** @var $model Feature */
        $model = $this->repo->getBy(['id' => $id],
            withException: true,
            exceptionMessage: __("exceptions.inventories.features.feature.not_found")
        );

        return FeatureResource::make(
            $this->service->update($model, $request->getDto())
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/inventory-features/{id}",
     *     tags={"Inventory features"},
     *     security={{"Basic": {}}},
     *     summary="Get info about inventory feature",
     *     operationId="GetInfoAboutInventoryFeature",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Parameter(ref="#/components/parameters/IDPath"),
     *
     *     @OA\Response(response=200, description="Feature data",
     *         @OA\JsonContent(ref="#/components/schemas/InventoryFeatureResource")
     *     ),
     *
     *      @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *      @OA\Response(response="404", description="Not Found", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *      @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function show($id): FeatureResource
    {
        $this->authorize(Permission\Inventory\Feature\FeatureReadPermission::KEY);

        return FeatureResource::make(
            $this->repo->getBy(['id' => $id], withException: true,
                exceptionMessage: __("exceptions.inventories.features.feature.not_found")
            )
        );
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/inventory-features/{id}",
     *     tags={"Inventory features"},
     *     security={{"Basic": {}}},
     *     summary="Delete inventory feature",
     *     operationId="DeleteInventoryFeatures",
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
        $this->authorize(Permission\Inventory\Feature\FeatureDeletePermission::KEY);

        /** @var $model Feature */
        $model = $this->repo->getBy(['id' => $id],
            withException: true,
            exceptionMessage: __("exceptions.inventories.features.feature.not_found")
        );

        if ($model->hasInventoryRelation()) {
            return $this->errorJsonMessage(
                __("exceptions.inventories.features.feature.has_inventory"),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $this->service->delete($model);

        return $this->successJsonMessage(null, Response::HTTP_NO_CONTENT);
    }
}
