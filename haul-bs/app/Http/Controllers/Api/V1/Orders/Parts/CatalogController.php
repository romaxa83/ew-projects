<?php

namespace App\Http\Controllers\Api\V1\Orders\Parts;

use App\Enums\Orders\Parts\DeliveryMethod;
use App\Enums\Orders\Parts\DeliveryType;
use App\Enums\Orders\Parts\OrderPaymentStatus;
use App\Enums\Orders\Parts\OrderSource;
use App\Enums\Orders\Parts\OrderStatus;
use App\Enums\Orders\Parts\PaymentMethod;
use App\Enums\Orders\Parts\PaymentTerms;
use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Orders\Parts\Catalog\PaymentMethodRequest;
use App\Http\Resources\Common\EnumResource;
use App\Repositories\Orders\Parts\OrderRepository;
use Carbon\CarbonImmutable;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CatalogController extends ApiController
{
    public function __construct(protected OrderRepository $repo)
    {}

    /**
     * @OA\Get(
     *     path="/api/v1/orders/parts/catalog/payment-terms",
     *     tags={"Parts order Catalog"},
     *     security={{"Basic": {}}},
     *     summary="Get payment terms list for order parts",
     *     operationId="GetPaymentTermsListForOrderParts",
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
    public function paymentTerms(): AnonymousResourceCollection
    {
        return EnumResource::collection(PaymentTerms::getValuesLabels());
    }

    /**
     * @OA\Get(
     *     path="/api/v1/orders/parts/catalog/payment-methods",
     *     tags={"Parts order Catalog"},
     *     security={{"Basic": {}}},
     *     summary="Get payment methods list for order parts",
     *     operationId="GetPaymentMethodsListForOrderParts",
     *     deprecated=false,
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Parameter(name="payment_terms", in="query", required=false,
     *         description="Order payment terms,(can be get here - /api/v1/orders/parts/catalog/payment-terms)",
     *         @OA\Schema(type="string", enum={"immediately","day_15", "day_30"})
     *     ),
     *     @OA\Parameter(name="for_add_payment", in="query", required=false,
     *          description="List for adding payments to an order",
     *          @OA\Schema(type="boolean")
     *     ),
     *
     *     @OA\Response(response=200, description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/OrderPaymentMethodResource")
     *     ),
     * )
     */
    public function paymentMethods(PaymentMethodRequest $request): AnonymousResourceCollection
    {
        $methods = PaymentMethod::getValuesLabels();
        if($request->validated('payment_terms') == PaymentTerms::Immediately()){
            $methods = PaymentMethod::forImmediately();
        }
        if(
            $request->validated('payment_terms') == PaymentTerms::Day_15()
            || $request->validated('payment_terms') == PaymentTerms::Day_30()
        ){
            $methods = PaymentMethod::forThen();
        }
        if($request->validated('for_add_payment')){
            $methods = PaymentMethod::forAddPayment();
        }

        return EnumResource::collection($methods);
    }

    /**
     * @OA\Get(
     *     path="/api/v1/orders/parts/catalog/payment-statuses",
     *     tags={"Parts order Catalog"},
     *     security={{"Basic": {}}},
     *     summary="Get payment status list for order parts",
     *     operationId="GetPaymentStatusListForOrderParts",
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
    public function paymentStatuses(): AnonymousResourceCollection
    {
        return EnumResource::collection(OrderPaymentStatus::getValuesLabels());
    }

    /**
     * @OA\Get(
     *     path="/api/v1/orders/parts/catalog/order-statuses",
     *     tags={"Parts order Catalog"},
     *     security={{"Basic": {}}},
     *     summary="Get status list for order parts",
     *     operationId="GetStatusListForOrderParts",
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
    public function orderStatuses(): AnonymousResourceCollection
    {
        return EnumResource::collection(OrderStatus::getValuesLabels());
    }

    /**
     * @OA\Get(
     *     path="/api/v1/orders/parts/catalog/sources",
     *     tags={"Parts order Catalog"},
     *     security={{"Basic": {}}},
     *     summary="Get source list for order parts",
     *     operationId="GetSourceListForOrderParts",
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
    public function orderSource(): AnonymousResourceCollection
    {
        return EnumResource::collection(OrderSource::getValuesLabels());
    }

    /**
     * @OA\Get(
     *     path="/api/v1/orders/parts/catalog/delivery-types",
     *     tags={"Parts order Catalog"},
     *     security={{"Basic": {}}},
     *     summary="Get delivery type list for order parts",
     *     operationId="GetDeliveryTypeForOrderParts",
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
    public function deliveryType(): AnonymousResourceCollection
    {
        return EnumResource::collection(DeliveryType::getValuesLabels());
    }

    /**
     * @OA\Get(
     *     path="/api/v1/orders/parts/catalog/delivery-methods",
     *     tags={"Parts order Catalog"},
     *     security={{"Basic": {}}},
     *     summary="Get delivery methods list for order parts",
     *     operationId="GetDeliveryMethodsForOrderParts",
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
    public function deliveryMethod(): AnonymousResourceCollection
    {
        return EnumResource::collection(DeliveryMethod::getValuesLabels());
    }

    /**
     * @OA\Get(
     *     path="/api/v1/orders/parts/catalog/statuses-to-switch/{id}",
     *     tags={"Parts order Catalog"},
     *     security={{"Basic": {}}},
     *     summary="Get statuses for switch",
     *     operationId="GetStatusesForSwitch",
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
    public function statusesToSwitch($id): AnonymousResourceCollection
    {
        $model = $this->repo->getById($id);

        $statuses = $model->status->toggleOn();

        if(
            $model->payment_terms?->isImmediately()
            && !$model->isPaid()
        ){
            $statuses = $this->removeStatus($statuses, OrderStatus::Sent());
        }

        if(
            $model->status->isDelivered()
            && $model->status_changed_at < CarbonImmutable::now()->subDays(config('orders.parts.change_status_delivered_to_returned'))
        ){
            $statuses = $this->removeStatus($statuses, OrderStatus::Returned());
        }

        if($model->status->isInProcess() && $model->delivery_type?->isPickup()){
            $statuses = $this->removeStatus($statuses, OrderStatus::Sent());
        }
        if(
            $model->status->isInProcess()
            && $model->source->isHaulkDepot()
            && !$model->isPaid()
        ){
            $statuses = $this->removeStatus($statuses, OrderStatus::Sent());
        }

        return EnumResource::collection($statuses);
    }

    private function removeStatus(array $statuses, string $removeStatus): array
    {
        $tmp = [];
        foreach ($statuses as $status){
            if($status['value'] != $removeStatus){
                $tmp[] = $status;
            }
        }

        return $tmp;
    }
}
