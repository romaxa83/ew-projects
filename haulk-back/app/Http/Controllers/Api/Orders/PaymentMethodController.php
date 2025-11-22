<?php

namespace App\Http\Controllers\Api\Orders;

use App\Http\Controllers\ApiController;
use App\Http\Resources\Orders\PaymentMethodMobileResource;
use App\Http\Resources\Orders\PaymentMethodResource;
use App\Models\Orders\Payment;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class PaymentMethodController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection
     *
     * @OA\Get(
     *     path="/api/payment-methods/for-order",
     *     tags={"Payment methods"},
     *     summary="Get payment methods list for order dropdown",
     *     operationId="Get payment methods list for order dropdown",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/PaymentMethodResource")
     *     ),
     * )
     *
     */
    public function forOrder(): AnonymousResourceCollection
    {
        return PaymentMethodResource::collection(
            Payment::getMethodsList()
        );
    }

    /**
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection
     *
     * @OA\Get(
     *     path="/api/payment-methods/for-driver",
     *     tags={"Payment methods"},
     *     summary="Get payment methods list for driver",
     *     operationId="Get payment methods list for driver",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/PaymentMethodResource")
     *     ),
     * )
     *
     */
    public function forDriver(): AnonymousResourceCollection
    {
        $methods = collect(Payment::BROKER_METHODS);

        return PaymentMethodMobileResource::collection(
            $methods->only(
                [
                    Payment::METHOD_QUICKPAY,
                    Payment::METHOD_CASHAPP,
                    Payment::METHOD_PAYPAL,
                    Payment::METHOD_VENMO,
                    Payment::METHOD_ZELLE,
                ]
            )->map(
                function ($title, $id) {
                    return (object) [
                        'id' => $id,
                        'title' => $title,
                    ];
                }
            )
        );
    }
}
