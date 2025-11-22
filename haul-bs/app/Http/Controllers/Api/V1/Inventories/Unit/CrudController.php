<?php

namespace App\Http\Controllers\Api\V1\Inventories\Unit;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Inventories\Unit\UnitRequest;
use App\Http\Resources\Inventories\Unit\UnitResource;
use App\Foundations\Modules\Permission\Permissions as Permission;
use App\Models\Inventories\Unit;
use App\Repositories\Inventories\UnitRepository;
use App\Services\Inventories\UnitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;

class CrudController extends ApiController
{
    public function __construct(
        protected UnitRepository $repo,
        protected UnitService $service,
    )
    {}

    /**
     * @OA\Get(
     *     path="/api/v1/inventory-units",
     *     tags={"Inventory units"},
     *     security={{"Basic": {}}},
     *     summary="Get inventory units list",
     *     operationId="GetInventoryUnitsList",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Response(response=200, description="Unit data",
     *         @OA\JsonContent(ref="#/components/schemas/UnitListResource")
     *     ),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function index(): ResourceCollection
    {
        $this->authorize(Permission\Inventory\Unit\UnitReadPermission::KEY);

        return UnitResource::collection(
            $this->repo->getList(
                sort: ['name' => 'asc']
            )
        );
    }

    /**
     * @OA\Post(
     *     path="/api/v1/inventory-units",
     *     tags={"Inventory units"},
     *     security={{"Basic": {}}},
     *     summary="Create inventory unit",
     *     operationId="CreateInventoryUnit",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(ref="#/components/schemas/UnitRequest")
     *     ),
     *
     *     @OA\Response(response=201, description="Unit data",
     *         @OA\JsonContent(ref="#/components/schemas/UnitResource")
     *     ),
     *
     *     @OA\Response(response="422", description="Validation", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function store(UnitRequest $request): UnitResource|JsonResponse
    {
        $this->authorize(Permission\Inventory\Unit\UnitCreatePermission::KEY);

        return UnitResource::make(
            $this->service->create($request->getDto())
        );
    }

    /**
     * @OA\Post(
     *     path="/api/v1/inventory-units/{id}",
     *     tags={"Inventory units"},
     *     security={{"Basic": {}}},
     *     summary="Update inventory unit",
     *     operationId="UpadteInventoryUnit",
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
     *         @OA\JsonContent(ref="#/components/schemas/UnitRequest")
     *     ),
     *
     *     @OA\Response(response=201, description="Unit data",
     *         @OA\JsonContent(ref="#/components/schemas/UnitResource")
     *     ),
     *
     *     @OA\Response(response="422", description="Validation", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="404", description="Not Found", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function update(UnitRequest $request, $id): UnitResource|JsonResponse
    {
        $this->authorize(Permission\Inventory\Unit\UnitUpdatePermission::KEY);

        /** @var $model Unit */
        $model = $this->repo->getBy(['id' => $id],
            withException: true,
            exceptionMessage: __("exceptions.inventories.unit.not_found")
        );

        return UnitResource::make(
            $this->service->update($model, $request->getDto())
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/inventory-units/{id}",
     *     tags={"Inventory units"},
     *     security={{"Basic": {}}},
     *     summary="Get info about inventory unit",
     *     operationId="GetInfoAboutInventoryUnit",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Parameter(ref="#/components/parameters/IDPath"),
     *
     *     @OA\Response(response=200, description="Unit data",
     *         @OA\JsonContent(ref="#/components/schemas/UnitResource")
     *     ),
     *
     *      @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *      @OA\Response(response="404", description="Not Found", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *      @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function show($id): UnitResource
    {
        $this->authorize(Permission\Inventory\Unit\UnitReadPermission::KEY);

        return UnitResource::make(
            $this->repo->getBy(['id' => $id], withException: true,
                exceptionMessage: __("exceptions.inventories.unit.not_found")
            )
        );
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/inventory-units/{id}",
     *     tags={"Inventory units"},
     *     security={{"Basic": {}}},
     *     summary="Delete inventory unit",
     *     operationId="DeleteInventoryUnit",
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
        $this->authorize(Permission\Inventory\Unit\UnitDeletePermission::KEY);

        /** @var $model Unit */
        $model = $this->repo->getBy(['id' => $id],
            withException: true,
            exceptionMessage: __("exceptions.inventories.unit.not_found")
        );

        if ($model->hasRelatedEntities()) {
            return $this->errorJsonMessage(
                __("exceptions.inventories.unit.has_related_entities"),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $this->service->delete($model);

        return $this->successJsonMessage(null, Response::HTTP_NO_CONTENT);
    }
}
