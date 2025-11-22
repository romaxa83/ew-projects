<?php

namespace App\Http\Controllers\Api\V1\Orders\BS;

use App\Foundations\Modules\Permission\Permissions as Permission;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Orders\BS\OrderPaymentRequest;
use App\Http\Resources\Orders\BS\OrderPaymentResource;
use App\Models\Orders\BS\Order;
use App\Models\Orders\BS\Payment;
use App\Repositories\Orders\BS\OrderRepository;
use App\Services\Orders\BS\OrderPaymentService;
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
     *     path="/api/v1/orders/bs/{id}/payment",
     *     tags={"BS order"},
     *     security={{"Basic": {}}},
     *     summary="Add payment to bodyshop order",
     *     operationId="AddPaymentToBSOrder",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(ref="#/components/parameters/ValidateOnly"),
     *
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(ref="#/components/schemas/OrderPaymentRequest")
     *     ),
     *
     *     @OA\Response(response=200, description="Order bodyshop payment data",
     *         @OA\JsonContent(ref="#/components/schemas/PaymentResourceRawBS")
     *     ),
     *
     *     @OA\Response(response="422", description="Validation", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function add(OrderPaymentRequest $request): OrderPaymentResource|JsonResponse
    {
        $this->authorize(Permission\Order\BS\OrderCreatePaymentPermission::KEY);

        return OrderPaymentResource::make(
            $this->service->add(
                $request->getOrder(),
                $request->getDto()
            )
        );
    }

    /**
     * @OA\Delete(
     *     path="/api/v1/orders/bs/{id}/payment/{paymentId}",
     *     tags={"BS order"},
     *     security={{"Basic": {}}},
     *     summary="Delete payment from bodyshop order",
     *     operationId="DeletePaymentFromBSOrder",
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
        $this->authorize(Permission\Order\BS\OrderDeletePaymentPermission::KEY);

        /** @var $model Order */
        $model = $this->repo->getBy(
            ['id' => $id],
            ['payments'],
            withException: true,
            exceptionMessage: __("exceptions.orders.bs.not_found"),
        );

        /** @var $payment Payment */
        if(!$payment = $model->payments()->where('id', $paymentId)->first()){
            throw new \Exception(__("exceptions.orders.bs.not_found_payment"), Response::HTTP_NOT_FOUND);
        }

        $this->service->delete($model, $payment);

        return $this->successJsonMessage(null, Response::HTTP_NO_CONTENT);
    }
}

