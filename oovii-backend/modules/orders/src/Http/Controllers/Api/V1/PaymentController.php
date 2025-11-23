<?php

namespace WezomCms\Orders\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Log;
use Throwable;
use WezomCms\Core\Http\Controllers\ApiController;
use WezomCms\Orders\Http\Resources\V1\PaymentResource;
use WezomCms\Orders\Repositories\PaymentsRepository;
use WezomCms\TelegramBot\Telegram;

class PaymentController extends ApiController
{
    public function __construct(
        private PaymentsRepository $paymentRepo,
    )
    {
        parent::__construct();
    }

    /**
     * @OA\Get (
     *     path="/mobile/payment-drivers",
     *     tags={"Order"},
     *     summary="Get payments list",
     *
     *     @OA\Response(response="200", description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="data", type="array",
     *                  @OA\Items(ref="#/components/schemas/PaymentResource")
     *              )
     *          )
     *     ),
     *     @OA\Response(response="401", description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function drivers(): JsonResponse
    {
        Telegram::info("ROUTE - /mobile/payment-drivers");
        try {

            return self::successJsonMessage(
                PaymentResource::collection($this->paymentRepo->getAllForFront())
            );
        } catch (Throwable $e) {
            Log::error($e->getMessage());

            return self::errorJsonMessage($e->getMessage());
        }
    }
}
