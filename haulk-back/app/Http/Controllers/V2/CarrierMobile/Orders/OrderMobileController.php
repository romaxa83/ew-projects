<?php


namespace App\Http\Controllers\V2\CarrierMobile\Orders;


use App\Http\Requests\Orders\CompleteInspectionRequest;
use App\Http\Requests\V2\Orders\DriverPaymentDataRequest;
use App\Http\Resources\Orders\OrderResource;
use App\Models\Orders\Order;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Throwable;

class OrderMobileController extends \App\Http\Controllers\Api\Orders\OrderMobileController
{

    /**
     * Edit single order payment
     * @param DriverPaymentDataRequest $request
     * @param Order $order
     * @return OrderResource|JsonResponse
     * @throws AuthorizationException
     * @throws Throwable
     * @OA\Post(
     *     path="/v2/carrier-mobile/orders/{orderId}/add-payment-data",
     *     tags={"Mobile Orders V2"},
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
     *     @OA\Parameter(name="driver_payment_account_type", in="query", required=false,
     *          @OA\Schema(type="string", enum={"personal", "company"})
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

    /**
     * @param Order $order
     * @param CompleteInspectionRequest $request
     * @return OrderResource|JsonResponse
     * @throws Throwable
     *
     * @OA\Post(
     *     path="/v2/carrier-mobile/orders/{orderId}/complete-inspection",
     *     tags={"Mobile Orders V2"},
     *     summary="Complete inspection for order with types: w/o inspection and w/o inspection (with bol)",
     *     operationId="Complete inspection for order with types: w/o inspection and w/o inspection (with bol)",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Parameter(name="inspection_type", in="query", description="Inspection type", required=true,
     *          @OA\Schema(type="string", enum={"pickup", "delivery"})
     *     ),
     *     @OA\Parameter(name="bol_file", in="query", description="File for order type: w/o inspection (with bol file) ", required=false,
     *          @OA\Schema(type="file",)
     *     ),
     *     @OA\Parameter(name="actual_date", in="query", description="inspection actual date unix timestamp", required=false,
     *          @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/OrderResource")
     *     ),
     * )
     */
    public function completeInspection(Order $order, CompleteInspectionRequest $request)
    {
        try {

            $order = $this->orderService->completeInspection($order, $request->validated());

            $order = $this->statusService()->autoChangeStatus($order);

            return OrderResource::make($order);
        } catch (Exception $e) {
            return $this->makeErrorResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
