<?php

namespace App\Http\Controllers\Api\OneC\Orders;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\OneC\Orders\OrderListRequest;
use App\Http\Requests\Api\OneC\Orders\OrderRequest;
use App\Http\Resources\Api\OneC\Orders\OrderResource;
use App\Models\Orders\Order;
use App\Services\Orders\OrderService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Throwable;

/**
 * @group Orders
 *
 * @enum App\Enums\Orders\OrderCostStatusEnum
 * @enum App\Enums\Orders\OrderStatusEnum
 */
class OrderController extends Controller
{
    /**
     * List
     *
     * @permission App\Permissions\Orders\OrderListPermission
     *
     * @responseFile docs/api/orders/list.json
     */
    public function index(OrderListRequest $request): AnonymousResourceCollection
    {
        return OrderResource::collection(
            Order::query()
                ->with('parts')
                ->with('shipping')
                ->with('payment')
                ->with('product:id,title')
                ->filter($request->validated())
                ->paginate()
        );
    }

    /**
     * Update
     *
     * @permission App\Permissions\Orders\OrderUpdatePermission
     * @responseFile docs/api/orders/single.json
     * @throws Throwable
     */
    public function update(Order $order, OrderRequest $request, OrderService $service): OrderResource
    {
        return OrderResource::make(
            makeTransaction(
                static fn() => $service->updateByApi($order, $request->getDto())
            )
        );
    }
}
