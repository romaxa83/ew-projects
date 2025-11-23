<?php

namespace WezomCms\Orders\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Log;
use Throwable;
use WezomCms\Core\Http\Controllers\ApiController;
use WezomCms\Orders\Http\Resources\V1\DeliveryResource;
use WezomCms\Orders\Repositories\DeliveriesRepository;
use WezomCms\TelegramBot\Telegram;

class DeliveryController extends ApiController
{
    public function __construct(
        private DeliveriesRepository $deliveryRepo,
    )
    {
        parent::__construct();
    }

    /**
     * @OA\Get (
     *     path="/mobile/delivery-drivers",
     *     tags={"Delivery"},
     *     summary="Get deliveries list",
     *
     *     @OA\Response(response="200", description="OK",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", title="data", type="array",
     *                  @OA\Items(ref="#/components/schemas/DeliveryResource")
     *              )
     *          )
     *     ),
     *     @OA\Response(response="401", description="Unauthorized", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/ErrorResponse")),
     * )
     */
    public function drivers(): JsonResponse
    {
        Telegram::info("ROUTE - /mobile/delivery-drivers");
        try {
            return self::successJsonMessage(
                DeliveryResource::collection($this->deliveryRepo->getAllForFront()),
            );
        } catch (Throwable $e) {
            Log::error($e->getMessage());
            return self::errorJsonMessage($e->getMessage());
        }
    }
}
