<?php

namespace App\Http\Controllers\Api\OneC\Orders\DeliveryTypes;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\OneC\Orders\DeliveryTypes\OrderDeliveryTypeListRequest;
use App\Http\Resources\Api\OneC\Orders\DeliveryTypes\DeliveryTypeResource;
use App\Models\Orders\Deliveries\OrderDeliveryType;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @group Order DeliveryTypes
 */
class OrderDeliveryTypeController extends Controller
{
    /**
     * List
     *
     * @permission App\Permissions\Orders\DeliveryTypes\OrderDeliveryTypeListPermission
     * @responseFile docs/api/orders/deliveryTypes/list.json
     */
    public function index(OrderDeliveryTypeListRequest $request): AnonymousResourceCollection
    {
        return DeliveryTypeResource::collection(
            OrderDeliveryType::query()
                ->filter($request->validated())
                ->get()
        );
    }
}
