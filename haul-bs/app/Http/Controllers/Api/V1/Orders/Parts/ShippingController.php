<?php

namespace App\Http\Controllers\Api\V1\Orders\Parts;

use App\Enums\Orders\Parts\ShippingMethod;
use App\Foundations\Modules\Permission\Permissions as Permission;
use App\Http\Controllers\Api\ApiController;
use App\Http\Resources\Orders\Parts\Shipping\ShippingMethodResource;
use App\Http\Resources\Orders\Parts\Shipping\ShippingResource;
use App\Models\Orders\Parts\Order;
use App\Repositories\Orders\Parts\OrderRepository;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ShippingController extends ApiController
{
    public function __construct(
        protected OrderRepository $repo,
    )
    {}

    /**
     * @OA\Get(
     *     path="/api/v1/orders/parts/{id}/shipping-methods",
     *     tags={"Parts order"},
     *     security={{"Basic": {}}},
     *     summary="Get Shipping method for part order",
     *     operationId="GetShippingMethodForPartOrder",
     *
     *     @OA\Parameter(ref="#/components/parameters/Content-type"),
     *     @OA\Parameter(ref="#/components/parameters/Accept"),
     *     @OA\Parameter(ref="#/components/parameters/Authorization"),
     *
     *     @OA\Response(response=200, description="Shipping method for Order parts",
     *         @OA\JsonContent(ref="#/components/schemas/ShippingResource")
     *     ),
     *
     *     @OA\Response(response="422", description="Validation", @OA\JsonContent(ref="#/components/schemas/ValidationErrors")),
     *     @OA\Response(response="403", description="Forbbiden", @OA\JsonContent(ref="#/components/schemas/Errors")),
     *     @OA\Response(response="500", description="Server Error", @OA\JsonContent(ref="#/components/schemas/Errors")),
     * )
     */
    public function getMethods($id): ResourceCollection
    {
        $this->authorize(Permission\Order\Parts\OrderUpdatePermission::KEY);

        $data = [];

        /** @var $model Order */
        $model = $this->repo->getById($id);

        if($model->items->count() < 1){
            return ShippingMethodResource::collection($data);
        }

        if($model->delivery_type->isPickup()){
            $data[] = [
                'methods' => [config('shipping.methods.test_data')[ShippingMethod::Pickup()]],
                'items' => $model->items
            ];
        }

        // у заказа товары только с бесплатной доставкой
        if(
            $model->delivery_type->isDelivery()
            && $model->hasFreeShippingInventory()
            && !$model->hasPaidShippingInventory()
        ){
            $data[] = [
                'methods' => [config('shipping.methods.test_data')[ShippingMethod::UPS_Standard()]],
                'items' => $model->itemsFreeShipping()
            ];
        }

        // у заказа товары только с платной доставкой
        if(
            $model->delivery_type->isDelivery()
            && !$model->hasFreeShippingInventory()
            && $model->hasPaidShippingInventory()
        ){
            $methods = [];
            // todo здесь будут запросы в службы доставки, сейчас тестовые данные
            if(config('shipping.methods.enable_test_data')){
                $methods = [
                    config('shipping.methods.test_data')[ShippingMethod::UPS_Next_Day_Air_Saver()],
                    config('shipping.methods.test_data')[ShippingMethod::UPS_Next_Day_Air()],
                    config('shipping.methods.test_data')[ShippingMethod::FedEx_Ground()],
                    config('shipping.methods.test_data')[ShippingMethod::FedEx_Express_Saver()],
                ];
            }

            $data[] = [
                'methods' => $methods,
                'items' => $model->itemsPaidShipping()
            ];
        }

        // у заказа товары с платной и бесплатной доставкой
        if(
            $model->delivery_type->isDelivery()
            && $model->hasFreeShippingInventory()
            && $model->hasPaidShippingInventory()
        ){
             $methods_free = [config('shipping.methods.test_data')[ShippingMethod::UPS_Standard()]];
             $methods_paid = [];
            // todo здесь будут запросы в службы доставки, сейчас тестовые данные
            if(config('shipping.methods.enable_test_data')){
                $methods_paid = [
                    config('shipping.methods.test_data')[ShippingMethod::UPS_Next_Day_Air_Saver()],
                    config('shipping.methods.test_data')[ShippingMethod::UPS_Next_Day_Air()],
                    config('shipping.methods.test_data')[ShippingMethod::FedEx_Ground()],
                    config('shipping.methods.test_data')[ShippingMethod::FedEx_Express_Saver()],
                ];
            }

            $data[] = [
                'methods' => $methods_free,
                'items' => $model->itemsFreeShipping()
            ];
            $data[] = [
                'methods' => $methods_paid,
                'items' => $model->itemsPaidShipping()
            ];
        }

        return ShippingResource::collection($data);
    }
}
