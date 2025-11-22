<?php

namespace App\Http\Controllers\Api\V1\Orders\Parts;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Orders\Parts\Ecom;
use App\Http\Resources\Orders\Parts\OrderResource;
use App\Services\Orders\Parts\OrderService;
use App\Services\Payments\PaymentService;

class EComController extends ApiController
{
    public function __construct(
        protected OrderService $service,
        protected PaymentService $paymentService,
    )
    {}

    /**
     * @OA\Post(
     *     path="/api/v1/e-comm/orders",
     *     tags={"E-Commerce"},
     *     security={{"Basic": {}}},
     *     summary="Add order from e-comm",
     *     operationId="AddOrderFromEcomm",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization_EComm"),
     *
     *     @OA\RequestBody(required=true,
     *         @OA\JsonContent(ref="#/components/schemas/OrderPartsEcomRequest")
     *     ),
     *
     *     @OA\Response(response=200, description="Order parts data",
     *         @OA\JsonContent(
     *             @OA\Property(property="order", type="object", ref="#/components/schemas/OrderPartsResource"),
     *             @OA\Property(property="link", type="string"),
     *         )
     *     ),
     *
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function store(Ecom\OrderPartsEcomRequest $request): array
    {
        $order = $this->service->createFromEcomm($request->getDto());

        return [
            'order' => OrderResource::make($order),
            'link' => $this->paymentService->getPaymentLink($order)
        ];
    }
}
