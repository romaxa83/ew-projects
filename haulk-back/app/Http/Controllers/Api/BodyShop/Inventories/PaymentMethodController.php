<?php

namespace App\Http\Controllers\Api\BodyShop\Inventories;

use App\Http\Controllers\ApiController;
use App\Http\Resources\BodyShop\Orders\PaymentMethodResource;
use App\Models\BodyShop\Inventories\Transaction;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PaymentMethodController extends ApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection
     *
     * @OA\Get(
     *     path="/api/body-shop/inventories/payment-methods",
     *     tags={"Payment methods Body Shop"},
     *     summary="Get payment methods list for inventory transactions",
     *     operationId="Get payment methods list for inventory transactions",
     *     deprecated=false,
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Admin-panel"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/PaymentMethodResourceBS")
     *     ),
     * )
     *
     */
    public function index(): AnonymousResourceCollection
    {
        return PaymentMethodResource::collection(
            Transaction::getPaymentMethodsList()
        );
    }
}
