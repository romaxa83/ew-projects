<?php


namespace App\Http\Controllers\V1\CarrierMobile\Orders;


use App\Http\Requests\Orders\DriverPaymentDataRequest;
use App\Http\Resources\Orders\OrderResource;
use App\Models\Orders\Order;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Throwable;
use Exception;

class OrderMobileController extends \App\Http\Controllers\Api\Orders\OrderMobileController
{

    /**
     * Edit single order payment
     *
     * @param DriverPaymentDataRequest $request
     * @param Order $order
     * @return OrderResource|JsonResponse
     *
     * @OA\Post(
     *     path="/v1/carrier-mobile/orders/{orderId}/add-payment-data",
     *     tags={"Mobile Orders"},
     *     summary="Add order payment details",
     *     operationId="Add order payment details",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="driver_payment_amount", in="query", required=false,
     *          @OA\Schema(type="number")
     *     ),
     *     @OA\Parameter(name="driver_payment_method_id", in="query", required=false,
     *          @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(name="driver_payment_uship_code", in="query", required=false,
     *          @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(name="driver_payment_check_photo", in="query", required=false,
     *          @OA\Schema(type="file")
     *     ),
     *     @OA\Parameter(name="driver_payment_comment", in="query", required=false,
     *          @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/OrderResource")
     *     ),
     * )
     *
     * @throws AuthorizationException
     * @throws Throwable
     */
    public function addPaymentData(DriverPaymentDataRequest $request, Order $order)
    {
        $this->authorize('orders inspection');
        $this->authorize('viewAssignedToMe', $order);

        try {
            $order = $this->orderService->addPaymentData($order, $request->validated());

            $order = $this->statusService()->autoChangeStatus($order);

            return OrderResource::make($order);
        } catch (Exception $e) {
            return $this->makeErrorResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
