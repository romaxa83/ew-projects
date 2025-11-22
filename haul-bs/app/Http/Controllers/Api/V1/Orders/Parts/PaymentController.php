<?php

namespace App\Http\Controllers\Api\V1\Orders\Parts;

use App\Foundations\Modules\Permission\Permissions as Permission;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Orders\Parts\OrderPartsPaymentRequest;
use App\Http\Resources\Orders\Parts\OrderPaymentResource;
use App\Models\Orders\Parts\Order;
use App\Models\Orders\Parts\Payment;
use App\Repositories\Orders\Parts\OrderRepository;
use App\Services\Orders\Parts\OrderPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class PaymentController extends ApiController
{
    public function __construct(
        protected OrderRepository $repo,
        protected OrderPaymentService $service,
    )
    {}

    /**
     * @OA\Post(
     *     path="/api/v1/orders/parts/{id}/payment",
     *     tags={"Parts order"},
     *     security={{"Basic": {}}},
     *     summary="Add payment to parts order",
     *     operationId="AddPaymentToPartsOrder",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(ref="#/components/schemas/OrderPartsPaymentRequest")
     *     ),
     *
     *     @OA\Response(response=200, description="Order parts payment data",
     *         @OA\JsonContent(ref="#/components/schemas/PaymentResourceRawBS")
     *     ),
     *
     *     @OA\Response(response="422", description="Validation", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function add(OrderPartsPaymentRequest $request): OrderPaymentResource|JsonResponse
    {
        $this->authorize(Permission\Order\Parts\OrderCreatePaymentPermission::KEY);

        if (!$request->getOrder()->canAddPayment()) {
            return $this->errorJsonMessage(
                __("exceptions.orders.cant_add_payment"),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        Order::assertSalesManager($request->getOrder());

        return OrderPaymentResource::make(
            $this->service->add(
                $request->getOrder(),
                $request->getDto()
            )
        );
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/orders/parts/{id}/payment/{paymentId}",
     *     tags={"Parts order"},
     *     security={{"Basic": {}}},
     *     summary="Delete payment from parts order",
     *     operationId="DeletePaymentFromPartsOrder",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Parameter(ref="#/components/parameters/IDPath"),
     *
     *     @OA\Parameter(name="{paymentId}", in="path", required=true, description="ID payment entity",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Response(response=204, description="Successful delete"),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="404", description="Not Found", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function delete($id, $paymentId): JsonResponse
    {
        $this->authorize(Permission\Order\Parts\OrderDeletePaymentPermission::KEY);

        /** @var $model Order */
        $model = $this->repo->getById($id);

        Order::assertSalesManager($model);

        /** @var $payment Payment */
        if(!$payment = $model->payments()->where('id', $paymentId)->first()){
            throw new \Exception(__("exceptions.orders.parts.not_found_payment"), Response::HTTP_NOT_FOUND);
        }

        $this->service->delete($model, $payment);

        return $this->successJsonMessage(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/orders/parts/{id}/payment-send-link",
     *     tags={"Parts order"},
     *     security={{"Basic": {}}},
     *     summary="Send payment link to customer",
     *     operationId="SendPaymentLinkToCustomerByPartsOrder",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *
     *     @OA\Response(response=200, description="Successful operation"),
     *
     *     @OA\Response(response="422", description="Validation", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function sendLink($id): JsonResponse
    {
        $this->authorize(Permission\Order\Parts\OrderSendPaymentLinkPermission::KEY);

        /** @var $model Order */
        $model = $this->repo->getById($id);

        Order::assertSalesManager($model);

        if(!$model->canSendPaymentLink()) {

            return $this->errorJsonMessage(
                "Order is paid",
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $this->service->sendLink($model);

        return $this->successJsonMessage();
    }
}

