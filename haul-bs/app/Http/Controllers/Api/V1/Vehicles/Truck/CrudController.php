<?php

namespace App\Http\Controllers\Api\V1\Vehicles\Truck;

use App\Foundations\Modules\Permission\Permissions as Permission;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Vehicles\Truck\TruckFilterRequest;
use App\Http\Requests\Vehicles\Truck\TruckRequest;
use App\Http\Resources\Vehicles\Truck\TruckPaginationResource;
use App\Http\Resources\Vehicles\Truck\TruckResource;
use App\Models\Vehicles\Truck;
use App\Repositories\Vehicles\TruckRepository;
use App\Services\Vehicles\TruckService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;

class CrudController extends ApiController
{
    public function __construct(
        protected TruckRepository $repo,
        protected TruckService $service,
    )
    {}

    /**
     * @OA\Get(
     *     path="/api/v1/trucks",
     *     tags={"Vehicles truck"},
     *     security={{"Basic": {}}},
     *     summary="Get trucks pagination",
     *     operationId="GetTrucksPagination",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Parameter(ref="#/components/parameters/Page"),
     *     @OA\Parameter(ref="#/components/parameters/PerPage"),
     *
     *     @OA\Parameter(ref="#/components/parameters/OrderType"),
     *     @OA\Parameter(name="order_by", in="query", description="Field for sort", required=false,
     *         @OA\Schema(type="string", default="status", enum ={"company_name"})
     *     ),
     *
     *     @OA\Parameter(name="search", in="query", required=false,
     *         description="Scope for search by vin, unit number, licance plate, temporary plate",
     *         @OA\Schema( type="string", example="name",)
     *     ),
     *     @OA\Parameter(name="tag_id", in="query", description="Tag id", required=false,
     *         @OA\Schema( type="integer", example="1",)
     *     ),
     *     @OA\Parameter(name="customer_id", in="query", description="Customer id", required=false,
     *          @OA\Schema( type="integer", example="1",)
     *     ),
     *
     *     @OA\Response(response=200, description="Truck paginated data",
     *         @OA\JsonContent(ref="#/components/schemas/TruckPaginationResource")
     *     ),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function index(TruckFilterRequest $request): ResourceCollection
    {
        $this->authorize(Permission\Truck\TruckReadPermission::KEY);

        return TruckPaginationResource::collection(
            $this->repo->customPagination(
                relation: ['customer', 'comments', 'tags'],
                filters: $request->validated()
            )
        );
    }

    /**
     * @OA\Post(
     *     path="/api/v1/trucks",
     *     tags={"Vehicles truck"},
     *     security={{"Basic": {}}},
     *     summary="Create truck",
     *     operationId="CreateTruck",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(ref="#/components/schemas/TruckRequest")
     *     ),
     *
     *     @OA\Response(response=201, description="Truck data",
     *         @OA\JsonContent(ref="#/components/schemas/TruckResource")
     *     ),
     *
     *     @OA\Response(response="422", description="Validation", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function store(TruckRequest $request): TruckResource|JsonResponse
    {
        $this->authorize(Permission\Truck\TruckCreatePermission::KEY);

        return TruckResource::make(
            $this->service->create($request->getDto())
        );
    }

    /**
     * @OA\Post(
     *     path="/api/v1/trucks/{id}",
     *     tags={"Vehicles truck"},
     *     security={{"Basic": {}}},
     *     summary="Update truck",
     *     operationId="UpadteTucks",
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
     *         @OA\JsonContent(ref="#/components/schemas/TruckRequest")
     *     ),
     *
     *     @OA\Response(response=201, description="Truck data",
     *         @OA\JsonContent(ref="#/components/schemas/TruckResource")
     *     ),
     *
     *     @OA\Response(response="422", description="Validation", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="404", description="Not Found", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function update(TruckRequest $request): TruckResource|JsonResponse
    {
        $this->authorize(Permission\Truck\TruckUpdatePermission::KEY);

        return TruckResource::make(
            $this->service->update($request->getModel(), $request->getDto())
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/trucks/{id}",
     *     tags={"Vehicles truck"},
     *     security={{"Basic": {}}},
     *     summary="Get info about trucks",
     *     operationId="GetInfoAboutTrucks",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Parameter(ref="#/components/parameters/IDPath"),
     *
     *     @OA\Response(response=200, description="Truck data",
     *         @OA\JsonContent(ref="#/components/schemas/TruckResource")
     *     ),
     *
     *      @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *      @OA\Response(response="404", description="Not Found", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *      @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function show($id): TruckResource
    {
        $this->authorize(Permission\Truck\TruckReadPermission::KEY);

        return TruckResource::make(
            $this->repo->getBy(['id' => $id], withException: true,
                exceptionMessage: __("exceptions.vehicles.truck.not_found")
            )
        );
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/trucks/{id}",
     *     tags={"Vehicles truck"},
     *     security={{"Basic": {}}},
     *     summary="Delete trucks",
     *     operationId="DeleteTrucks",
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
        $this->authorize(Permission\Truck\TruckDeletePermission::KEY);

        /** @var $model Truck */
        $model = $this->repo->getBy(['id' => $id],
            withException: true,
            exceptionMessage: __("exceptions.vehicles.truck.not_found")
        );

        if ($model->hasRelatedOpenOrders() || $model->hasRelatedDeletedOrders()) {
            return $this->errorJsonMessage($this->getMessageForDeleteFailed($model),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $this->service->delete($model);

        return $this->successJsonMessage(null, Response::HTTP_NO_CONTENT);
    }

    protected function getMessageForDeleteFailed(Truck $model): string
    {
        if($model->hasRelatedOpenOrders() && $model->hasRelatedDeletedOrders()){
            $openOrderLink = str_replace('{id}', $model->id, config('routes.front.bs_open_orders_with_truck_filter_url'));
            $deleteOrderLink = str_replace('{id}', $model->id, config('routes.front.bs_deleted_orders_with_truck_filter_url'));

            return __("exceptions.vehicles.truck.has_open_and_deleted_orders", [
                'open_orders' => $openOrderLink,
                'deleted_orders' => $deleteOrderLink,
            ]);
        } elseif ($model->hasRelatedDeletedOrders()){
            $deleteOrderLink = str_replace('{id}', $model->id, config('routes.front.bs_deleted_orders_with_truck_filter_url'));

            return __("exceptions.vehicles.truck.has_deleted_orders", [
                'deleted_orders' => $deleteOrderLink,
            ]);
        }

        $openOrderLink = str_replace('{id}', $model->id, config('routes.front.bs_open_orders_with_truck_filter_url'));

        return __("exceptions.vehicles.truck.has_open_orders", [
            'open_orders' => $openOrderLink,
        ]);
    }
}
