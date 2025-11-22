<?php

namespace App\Http\Controllers\Api\V1\Orders\Parts;

use App\Enums\Orders\Parts\OrderStatus;
use App\Exceptions\Orders\Parts\ChangeOrderStatusException;
use App\Foundations\Modules\Permission\Permissions as Permission;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Orders\Parts\OrderAssignSalesManagerRequest;
use App\Http\Requests\Orders\Parts\OrderPartsChangeStatusRequest;
use App\Http\Requests\Orders\Parts\OrderPartsDeliveryRequest;
use App\Http\Resources\Orders\Parts\OrderResource;
use App\Models\Orders\Parts\Delivery;
use App\Models\Orders\Parts\Order;
use App\Models\Users\User;
use App\Repositories\Users\UserRepository;
use App\Services\Orders\Parts\DeliveryService;
use App\Services\Orders\Parts\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ActionController extends ApiController
{
    public function __construct(
        protected OrderService $service,
        protected DeliveryService $serviceDelivery,
        protected UserRepository $userRepo,
    )
    {}

    /**
     * @OA\Post(
     *     path="/api/v1/orders/parts/{id}/checkout",
     *     tags={"Parts order"},
     *     security={{"Basic": {}}},
     *     summary="Checkout parts order",
     *     operationId="CheckoutPartsOrder",
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *
     *     @OA\Parameter(ref="#/components/parameters/IDPath"),
     *
     *     @OA\Response(response=200, description="Order parts data",
     *         @OA\JsonContent(ref="#/components/schemas/OrderPartsResource")
     *     ),
     *
     *     @OA\Response(response="422", description="Validation", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function checkout($id): OrderResource|JsonResponse
    {
        $this->authorize(Permission\Order\Parts\OrderCreatePermission::KEY);

        /** @var $order Order */
        $order = $this->service->repo->getById($id);

        try {
            $order = $this->service->checkout($order, auth_user());
        } catch (\Throwable $e) {
            return $this->errorJsonMessage($e->getMessage(), $e->getCode());
        }

        return OrderResource::make($order);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/orders/parts/{id}/change-status",
     *     tags={"Parts order"},
     *     security={{"Basic": {}}},
     *     summary="Change status for parts order",
     *     operationId="ChangeStatusForPartsOrder",
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *
     *     @OA\Parameter(ref="#/components/parameters/IDPath"),
     *
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(ref="#/components/schemas/OrderPartsChangeStatusRequest")
     *     ),
     *
     *     @OA\Response(response=200, description="Order parts data",
     *         @OA\JsonContent(ref="#/components/schemas/OrderPartsResource")
     *     ),
     *
     *     @OA\Response(response="422", description="Validation", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function changeStatus(OrderPartsChangeStatusRequest $request, $id): OrderResource|JsonResponse
    {
        $this->authorize(Permission\Order\Parts\OrderChangeStatusPermission::KEY);

        /** @var $order Order */
        $order = $this->service->repo->getById($id);

        Order::assertSalesManager($order);

        if(!$order->sales_manager_id){
            return $this->errorJsonMessage(
                __("exceptions.orders.parts.cant_switch_status_not_sales"),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
        if(!$order->canChangeStatus()){
            return $this->errorJsonMessage(
                __("exceptions.orders.status_cant_be_change"),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        try {
            $order = $this->service->orderStatusService->changeStatus(
                $order,
                $request['status'],
                saveHistory: true,
                additionalData: $request->validated()
            );
        } catch (ChangeOrderStatusException $e) {
            return $this->errorJsonMessage(
                $e->getMessage(),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        } catch (\Throwable $e) {
            return $this->errorJsonMessage($e->getMessage(), $e->getCode());
        }

        return OrderResource::make($order);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/orders/parts/{id}/cancel",
     *     tags={"Parts order"},
     *     security={{"Basic": {}}},
     *     summary="Cancel parts order",
     *     operationId="CancelPartsOrder",
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *
     *     @OA\Parameter(ref="#/components/parameters/IDPath"),
     *
     *     @OA\Response(response=200, description="Order parts data",
     *         @OA\JsonContent(ref="#/components/schemas/OrderPartsResource")
     *     ),
     *
     *     @OA\Response(response="422", description="Validation", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function cancelOrder($id): OrderResource|JsonResponse
    {
        $this->authorize(Permission\Order\Parts\OrderChangeStatusPermission::KEY);

        /** @var $order Order */
        $order = $this->service->repo->getById($id);

        Order::assertSalesManager($order);

        if(!$order->canCanceled()){
            return $this->errorJsonMessage(
                __("exceptions.orders.status_cant_be_change"),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $order = $this->service->orderStatusService->changeStatus(
            $order,
            OrderStatus::Canceled(),
            saveHistory: true
        );

        return OrderResource::make($order);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/orders/parts/{id}/assign-sales-manager",
     *     tags={"Parts order"},
     *     security={{"Basic": {}}},
     *     summary="Assign/reassign sales manager for parts order",
     *     operationId="AssignSalesManagerForPartsOrder",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/IDPath"),
     *
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(ref="#/components/schemas/OrderAssignSalesManagerRequest")
     *     ),
     *
     *     @OA\Response(response=200, description="Order poarts data",
     *         @OA\JsonContent(ref="#/components/schemas/OrderPartsResource")
     *     ),
     *
     *     @OA\Response(response="422", description="Validation", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function assignSalesManager(OrderAssignSalesManagerRequest $request, $id): OrderResource
    {
        $this->authorize(Permission\Order\Parts\OrderAssignSalesManagerPermission::KEY);

        /** @var $model Order */
        $model = $this->service->repo->getById($id);

        Order::assertSalesManager($model);

        /** @var $sales User */
        $sales = $this->userRepo->getBy(
            ['id' => $request['sales_manager_id']],
        );

        return OrderResource::make(
            $this->service->assignSalesManager($model, $sales)
        );
    }

    /**
     * @OA\Post(
     *     path="/api/v1/orders/parts/{id}/refunded",
     *     tags={"Parts order"},
     *     security={{"Basic": {}}},
     *     summary="Refunded payment for parts order",
     *     operationId="RefundedPaymentForPartsOrder",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/IDPath"),
     *
     *     @OA\Response(response=200, description="Order poarts data",
     *         @OA\JsonContent(ref="#/components/schemas/OrderPartsResource")
     *     ),
     *
     *     @OA\Response(response="422", description="Validation", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function refunded($id): OrderResource
    {
        $this->authorize(Permission\Order\Parts\OrderRefundedPermission::KEY);

        /** @var $model Order */
        $model = $this->service->repo->getById($id);

        Order::assertSalesManager($model);

        return OrderResource::make(
            $this->service->refunded($model)
        );
    }

    /**
     * @OA\Post(
     *     path="/api/v1/orders/parts/{id}/delivery/{deliveryId}",
     *     tags={"Parts order"},
     *     security={{"Basic": {}}},
     *     summary="Edit delivery cost for parts order",
     *     operationId="EditDeliveryCostForPartsOrder",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/IDPath"),
     *     @OA\Parameter(name="{deliveryId}", in="path", required=true, description="ID delivery",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(ref="#/components/schemas/OrderPartsDeliveryRequest")
     *     ),
     *
     *     @OA\Response(response=200, description="Order poarts data",
     *         @OA\JsonContent(ref="#/components/schemas/OrderPartsResource")
     *     ),
     *
     *     @OA\Response(response="422", description="Validation", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function editDelivery(OrderPartsDeliveryRequest $request, $id, $deliveryId): OrderResource
    {
        $this->authorize(Permission\Order\Parts\OrderUpdatePermission::KEY);

        /** @var $order Order */
        $order = $this->service->repo->getById($id);

        Order::assertSalesManager($order);

        /** @var $model Delivery */
        $model = $order->deliveries()->where('id', $deliveryId)->first();

        $this->serviceDelivery->update($model, $request->getDto());

        return OrderResource::make($order->refresh());
    }
}
