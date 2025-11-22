<?php

namespace App\Http\Controllers\Api\V1\Orders;

use App\Enums\Orders\Parts\PaymentTerms;
use App\Enums\Orders\PaymentMethod;
use App\Foundations\Enums\EnumHelper;
use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\Orders\PaymentMethodResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PaymentMethodController extends ApiController
{
    /**
     * @OA\Get(
     *     path="/api/v1/orders/payment-methods",
     *     tags={"Order Payment methods"},
     *     security={{"Basic": {}}},
     *     summary="Get payment methods list for order",
     *     operationId="GetPaymentMethodsListForOrder",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/OrderPaymentMethodResource")
     *     ),
     * )
     */
    public function index(): AnonymousResourceCollection
    {
        return PaymentMethodResource::collection(
            EnumHelper::resourceList(PaymentMethod::class)
        );
    }
}
