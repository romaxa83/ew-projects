<?php


namespace WezomCms\Orders\Http\Controllers\Api\V1;


use Exception;
use Illuminate\Http\JsonResponse;
use Log;
use Throwable;
use WezomCms\Core\Http\Controllers\ApiController;
use WezomCms\Orders\Events\CanceledOrder;
use WezomCms\Orders\Http\Requests\Api\V1\CheckoutRequest;
use WezomCms\Orders\Http\Requests\Api\V1\OrderPaymentRequest;
use WezomCms\Orders\Http\Resources\V1\PaymentInfoResource;
use WezomCms\Orders\Models\Order;
use WezomCms\Orders\Models\OrderPaymentInformation;
use WezomCms\Orders\Models\OrderStatus;
use WezomCms\Orders\Repositories\OrdersRepository;
use WezomCms\TelegramBot\Telegram;

class CheckoutController extends ApiController
{
    public function __construct(
        private OrdersRepository $ordersRepository
    )
    {
        parent::__construct();
    }

    /**
     * @OA\Post (
     *     path="/mobile/checkout/create-order",
     *     security={{"Basic": {}}},
     *     tags={"Order"},
     *
     *     summary="Create order",
     *     @OA\RequestBody(required=true,
     *          @OA\JsonContent(ref="#/components/schemas/CheckoutRequest")
     *     ),
     *
     *     @OA\Response(response="200", description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="data", type="object",
     *                  ref="#/components/schemas/PaymentInfoResource"
     *              ),
     *              @OA\Property(property="success", title="Success", example=true),
     *              @OA\Property(property="code", title="Code", example=0),
     *          )
     *     ),
     *     @OA\Response(response="400", description="Bad Request", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     * @param CheckoutRequest $request
     * @return JsonResponse
     */
    public function createOrder(CheckoutRequest $request): JsonResponse
    {
        Telegram::info("ROUTE - POST /mobile/checkout/create-order");
        try {
            $paymentInfo = $this->ordersRepository->createOrders($request);

            return self::successJsonMessage(PaymentInfoResource::make($paymentInfo));
        } catch (Throwable $e) {
            Log::error($e->getMessage());

            return self::errorJsonMessage($e->getMessage());
        }
    }

    /**
     * @OA\Get (
     *     path="/mobile/checkout/cancel-order/{order}",
     *     security={{"Basic": {}}},
     *     tags={"Order"},
     *     summary="Cancel order",
     *
     *     @OA\Parameter(name="order", in="path", required=true, description="ID заказа",
     *         @OA\Schema(type="integer", example=10)
     *     ),
     *
     *     @OA\Response(response="200", description="OK",
     *          @OA\JsonContent(ref="#/components/schemas/SuccessResponse")
     *     ),
     *
     *     @OA\Response(response="401", description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     * @param Order $order
     * @return JsonResponse
     */
    public function cancelOrder(Order $order): JsonResponse
    {
        Telegram::info("ROUTE - POST /mobile/checkout/cancel-order/{order}");
        try {
            $this->authorize('update', $order);

            $order->changeStatus(OrderStatus::canceledStatus())->save();

            event(new CanceledOrder($order));

            return self::successJsonMessage(__('cms-orders::site.Your order has been canceled'));
        } catch(Exception $e){
            Log::error($e->getMessage());

            return self::errorJsonMessage($e->getMessage(), errorCode($e));
        }
    }

    public function orderPayment(OrderPaymentInformation $paymentInfo, OrderPaymentRequest $request): JsonResponse
    {
        dump($request->all());
        dd($paymentInfo);
    }
}
