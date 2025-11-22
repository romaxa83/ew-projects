<?php

namespace App\Http\Controllers\Api\V1\Orders\BS;

use App\Foundations\Modules\Permission\Permissions as Permission;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Orders\BS\OrderFilterRequest;
use App\Http\Requests\Orders\BS\OrderRequest;
use App\Http\Resources\Orders\BS\OrderPaginationResource;
use App\Http\Resources\Orders\BS\OrderResource;
use App\Models\Orders\BS\Order;
use App\Repositories\Orders\BS\OrderRepository;
use App\Services\Orders\BS\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;

class CrudController extends ApiController
{
    public function __construct(
        protected OrderRepository $repo,
        protected OrderService $service,
    )
    {}

    /**
     * @OA\Get(
     *     path="/api/v1/orders/bs",
     *     tags={"BS order"},
     *     security={{"Basic": {}}},
     *     summary="Get bodyshop orders pagination",
     *     operationId="GetBSOrdersPagination",
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
     *     @OA\Parameter(name="mechanic_id", in="query", description="Order mechanic id", required=false,
     *         @OA\Schema( type="integer", default="1", )
     *     ),
     *     @OA\Parameter(name="vehicle_make", in="query", description="Vehicle make", required=false,
     *         @OA\Schema( type="string", default="name",)
     *     ),
     *     @OA\Parameter(name="vehicle_model", in="query", description="Vehicle model", required=false,
     *         @OA\Schema( type="string", default="name",)
     *     ),
     *     @OA\Parameter(name="vehicle_year", in="query", description="Vehicle year", required=false,
     *         @OA\Schema( type="string", default="name",)
     *     ),
     *     @OA\Parameter(name="status", in="query", description="Order status", required=false,
     *         @OA\Schema( type="string", enum={"new","in_process","finished","deleted"})
     *     ),
     *     @OA\Parameter(name="payment_status", in="query", description="Order payment status", required=false,
     *         @OA\Schema( type="string", enum={"paid","not_paid","billed","not_billed","overdue","not_overdue"})
     *     ),
     *     @OA\Parameter(name="date_from", in="query", description="Order implementation date from", required=false,
     *         @OA\Schema( type="string", default="2023-02-13 10:00",)
     *     ),
     *     @OA\Parameter(name="date_to", in="query", description="Order implementation date to", required=false,
     *         @OA\Schema( type="string", default="2023-02-13 10:00",)
     *     ),
     *     @OA\Parameter(name="inventory_id", in="query", description="Order inventory id", required=false,
     *         @OA\Schema( type="integer", default="1", )
     *     ),
     *     @OA\Parameter(name="truck_id", in="query", description="Order truck id", required=false,
     *         @OA\Schema( type="integer", default="1", )
     *     ),
     *     @OA\Parameter(name="trailer_id", in="query", description="Order trailer id", required=false,
     *         @OA\Schema( type="integer", default="1", )
     *     ),
     *
     *     @OA\Response(response=200, description="Order bodyshop data",
     *         @OA\JsonContent(ref="#/components/schemas/OrderBSPaginationResource")
     *     ),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function index(OrderFilterRequest $request): ResourceCollection
    {
        $this->authorize(Permission\Order\BS\OrderReadPermission::KEY);

        return OrderPaginationResource::collection(
            $this->repo->customPagination(
                filters: $request->validated(),
            )
        );
    }

    /**
     * @OA\Post(
     *     path="/api/v1/orders/bs",
     *     tags={"BS order"},
     *     security={{"Basic": {}}},
     *     summary="Create bodyshop order",
     *     operationId="CreateBSOrder",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(ref="#/components/schemas/OrderRequest")
     *     ),
     *
     *     @OA\Response(response=200, description="Order bodyshop data",
     *         @OA\JsonContent(ref="#/components/schemas/OrderBSResource")
     *     ),
     *
     *     @OA\Response(response="422", description="Validation", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function store(OrderRequest $request): OrderResource|JsonResponse
    {
        $this->authorize(Permission\Order\BS\OrderCreatePermission::KEY);

        return OrderResource::make(
            $this->service->create(
                $request->getDto(),
            )
        );
    }

    /**
     * @OA\Get(
     *     path="/api/v1/orders/bs/{id}",
     *     tags={"BS order"},
     *     security={{"Basic": {}}},
     *     summary="Get info about bodyshop order",
     *     operationId="GetInfoAboutBSOrder",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Parameter(ref="#/components/parameters/IDPath"),
     *
     *     @OA\Response(response=200, description="Order bodyshop data",
     *         @OA\JsonContent(ref="#/components/schemas/OrderBSResource")
     *     ),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="404", description="Not Found", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function show($id): OrderResource
    {
        $this->authorize(Permission\Order\BS\OrderReadPermission::KEY);

        return OrderResource::make(
            $this->repo->getBy(
                ['id' => $id],
                withException: true,
                exceptionMessage: __("exceptions.orders.bs.not_found"),
            )
        );
    }

    /**
     * @OA\Post(
     *     path="/api/v1/orders/bs/{id}",
     *     tags={"BS order"},
     *     security={{"Basic": {}}},
     *     summary="Update bodyshop order",
     *     operationId="UpdateBSOrder",
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
     *         @OA\JsonContent(ref="#/components/schemas/OrderRequest")
     *     ),
     *
     *     @OA\Response(response=200, description="Order bodyshop data",
     *         @OA\JsonContent(ref="#/components/schemas/OrderBSResource")
     *     ),
     *
     *     @OA\Response(response="422", description="Validation", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function update(OrderRequest $request, $id): OrderResource|JsonResponse
    {
        $this->authorize(Permission\Order\BS\OrderUpdatePermission::KEY);

        /** @var $model Order */
        $model = $this->repo->getBy(
            ['id' => $id],
            withException: true,
            exceptionMessage: __("exceptions.orders.bs.not_found"),
        );

        if ($model->status->isFinished()) {
            return $this->errorJsonMessage(
                __("exceptions.orders.bs.finished_order_cant_be_edited"),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        if ($model->isPaid()) {
            return $this->errorJsonMessage(
                __("exceptions.orders.bs.paid_order_cant_be_edited"),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        return OrderResource::make(
            $this->service->update(
                $model, $request->getDto(),
            )
        );
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/orders/bs/{id}",
     *     tags={"BS order"},
     *     security={{"Basic": {}}},
     *     summary="Delete bodyshop order",
     *     operationId="DeleteBSOrder",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Parameter(ref="#/components/parameters/IDPath"),
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
        $this->authorize(Permission\Order\BS\OrderDeletePermission::KEY);

        /** @var $model Order */
        $model = $this->repo->getBy(
            ['id' => $id],
            withException: true,
            exceptionMessage: __("exceptions.orders.bs.not_found"),
        );

        if ($model->status->isFinished()) {
            return $this->errorJsonMessage(
                __("exceptions.orders.bs.finished_order_cant_be_deleted"),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $this->service->delete($model);

        return $this->successJsonMessage(null, Response::HTTP_NO_CONTENT);
    }
}
