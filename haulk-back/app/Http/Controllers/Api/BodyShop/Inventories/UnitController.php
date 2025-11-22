<?php

namespace App\Http\Controllers\Api\BodyShop\Inventories;

use App\Exceptions\HasRelatedEntitiesException;
use App\Http\Controllers\ApiController;
use App\Http\Requests\BodyShop\Inventories\UnitRequest;
use App\Http\Resources\BodyShop\Inventories\UnitResource;
use App\Models\BodyShop\Inventories\Unit;
use App\Services\BodyShop\Inventories\UnitService;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Throwable;

class UnitController extends ApiController
{
    protected UnitService $service;

    public function __construct(UnitService $service)
    {
        parent::__construct();

        $this->service = $service;
    }

    /**
     * @param Request $request
     * @return AnonymousResourceCollection
     *
     * @OA\Get(
     *     path="/api/body-shop/inventory-units",
     *     tags={"Inventory Units Body Shop"},
     *     summary="Get inventory units list",
     *     operationId="Get Inventory units data",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/InventoryUnitList"),
     *     )
     * )
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('inventory-units');

        $categories = Unit::query()
            ->orderBy('name')
            ->get();

        return UnitResource::collection($categories);
    }

    /**
     * @param UnitRequest $request
     * @return UnitResource|JsonResponse
     * @throws AuthorizationException
     * @throws Throwable
     * @OA\Post(path="/api/body-shop/inventory-units",
     *     tags={"Inventory Units Body Shop"},
     *     summary="Create Inventory Unit",
     *     operationId="Create Inventory Unit",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="name", in="query", description="Inventory Unit name", required=true,
     *          @OA\Schema(type="string", default="Category 1",)
     *     ),
     *     @OA\Parameter(name="accept_decimals", in="query", description="Inventory Unit accept decimals", required=true,
     *          @OA\Schema(type="boolean", default="true",)
     *     ),
     *     @OA\Response(response=201, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/InventoryUnit")
     *     ),
     * )
     */
    public function store(UnitRequest $request)
    {
        $this->authorize('inventory-units create');

        try {
            $unit = $this->service->create($request->validated());

            return UnitResource::make($unit);
        } catch (Exception $exception) {
            Log::error($exception);

            return $this->makeErrorResponse($exception->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     *
     * @OA\Get(
     *     path="/api/body-shop/inventory-units/{unitId}",
     *     tags={"Inventory Units Body Shop"},
     *     summary="Get inventory unit record",
     *     operationId="Get inventory unit record",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="Inventory Unit id",
     *          required=true,
     *          @OA\Schema(
     *              type="integer",
     *          )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/InventoryUnit")
     *     ),
     * )
     * @param Unit $inventoryUnit
     * @return UnitResource
     * @throws AuthorizationException
     */
    public function show(Unit $inventoryUnit): UnitResource
    {
        $this->authorize('inventory-units read');

        return UnitResource::make($inventoryUnit);
    }

    /**
     * Update the specified resource in storage.
     *
     * @OA\Put(
     *     path="/api/body-shop/inventory-units/{unitId}",
     *     tags={"Inventory Units Body Shop"},
     *     summary="Update inventory unit record",
     *     operationId="Update invettory unit",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="id", in="path", description="Inventory Unit id", required=true,
     *          @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(name="name", in="query", description="Inventory Unit name", required=true,
     *          @OA\Schema(type="string", default="Category 1",)
     *     ),
     *     @OA\Parameter(name="accept_decimals", in="query", description="Inventory Unit accept decimals", required=true,
     *          @OA\Schema(type="boolean", default="true",)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/InventoryUnit")
     *     ),
     * )
     * @param UnitRequest $request
     * @param Unit $inventoryUnit
     * @return UnitResource|JsonResponse
     * @throws AuthorizationException
     * @throws Throwable
     */
    public function update(UnitRequest $request, Unit $inventoryUnit)
    {
        $this->authorize('inventory-units update');

        try {
            $this->service->update($inventoryUnit, $request->validated());

            return UnitResource::make($inventoryUnit);
        } catch (Exception $e) {
            Log::error($e);

            return $this->makeErrorResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @OA\Delete(
     *     path="/api/body-shop/inventory-units/{unitId}",
     *     tags={"Inventory Units Body Shop"},
     *     summary="Delete inventory unit",
     *     operationId="Delete inventory unit",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="Inventory Unit id",
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
     * @param Unit $inventoryUnit
     * @return JsonResponse
     * @throws AuthorizationException
     */
    public function destroy(Unit $inventoryUnit): JsonResponse
    {
        $this->authorize('inventory-units delete');

        try {
            $this->service->destroy($inventoryUnit);

            return $this->makeSuccessResponse(null, Response::HTTP_NO_CONTENT);
        } catch (HasRelatedEntitiesException $exception) {
            return $this->makeErrorResponse(
                trans('This unit of measurement is used for some parts.'),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        } catch (Exception $e) {
            Log::error($e);

            return $this->makeErrorResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
