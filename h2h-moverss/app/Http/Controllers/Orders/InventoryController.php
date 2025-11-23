<?php

namespace App\Http\Controllers\Orders;

use App\Events\Orders\ClientEditInventory;
use App\Http\Controllers\Controller;
use App\Http\Requests\Order\Inventory;
use App\Http\Requests\Order\OrderInventoryRequest;
use App\Models\{Item, Order};
use App\Services\Orders\OrderInventoryService;
use DB;
use Illuminate\Http\{JsonResponse, Request};
use Throwable;

/**
 * Manage Inventory for order
 */
class InventoryController extends Controller
{

    public function __construct(protected OrderInventoryService $service)
    {}

    /**
     * Autocomplete Items and Group of inventory.
     * @param  Request  $request
     * @return JsonResponse
     */
    public function ajaxSearch(Request $request): JsonResponse
    {
        $q = strip_tags($request->q);
        $division_id = (int) $request->division_id;

        if ($request->type === 'item') {
            $model = new Item();
        } else {
            $model = new Item\Group();
        }

        return response()
            ->json([
                'success' => true,
                'data' => $model->autocomplete($q, $division_id),
            ]);
    }

    /**
     * Save Inventory for Order.
     * @param  OrderInventoryRequest  $request
     * @return JsonResponse
     * @throws \Throwable
     */
    public function ajaxSave(OrderInventoryRequest $request): JsonResponse
    {
        try {
            DB::transaction(function () use ($request, &$res) {
                $validated = $request->validated();

                $order = Order::with([
                    'inventories', 'client', 'inventories.children',
                ])->findOrFail($validated['order_id']);

                $records = $validated['records'] ?? [];

                $res = (new Order\Inventory())->saveRecords($order, $records);

                if (
                    $res['is_changed']
                    && $order->client
                    && $request->route()->getName() == 'customer.inventory.save'
                ) {
                    $order->client->addActivity('customer.inventory.save', [
                        'division_id' => $order->division_id,
                        'ip' => $request->ip(),
                        'order_id' => $validated['order_id'],
                    ]);
                }
            });


        } catch (Throwable $e) {

            return response()
                ->json([
                    'success' => false,
                    'msg' => $e->getMessage(),
                ]);
        }

        broadcast(new ClientEditInventory($request->get('order_id')));

        return response()
            ->json($res);
    }

    /**
     * test @see \Tests\Feature\Orders\Inventories\AddTest
     */
    public function add(Inventory\CreateRequest $request, $id): JsonResponse
    {
        $model = $this->service
            ->add()
            ->setOrder($id)
            ->handler($request->validated());

        $order = Order::query()
            ->withInventoriesFormat($id)
            ->findOrFail($id)
        ;

        return response()
            ->json([
                'success' => true,
                'record' => $order,
                'meta' => [
                    'inventory' => $model,
                ]
            ]);
    }

    /**
     * test @see \Tests\Feature\Orders\Inventories\EditTest
     */
    public function edit(Inventory\CreateRequest $request, $orderId, $inventoryId): JsonResponse
    {
        $this->service
            ->edit()
            ->setOrder($orderId)
            ->setInventory($inventoryId)
            ->handler($request->validated());

        $order = Order::query()
            ->withInventoriesFormat($orderId)
            ->findOrFail($orderId)
        ;

        return response()
            ->json([
                'success' => true,
                'record' => $order,
            ]);
    }

    /**
     * test @see \Tests\Feature\Orders\Inventories\DeleteTest
     */
    public function delete($orderId, $inventoryId): JsonResponse
    {
        $this->service
            ->delete()
            ->setInventory($inventoryId)
            ->setOrder($orderId)
            ->handler();

        $order = Order::query()
            ->withInventoriesFormat($orderId)
            ->findOrFail($orderId)
        ;

        return response()
            ->json([
                'success' => true,
                'record' => $order,
            ]);
    }

    /**
     * test @see \Tests\Feature\Orders\Inventories\SortTest
     */
    public function sort(Inventory\SortRequest $request, $orderId): JsonResponse
    {
        $this->service
            ->sort($request->validated());

        $order = Order::query()
            ->withInventoriesFormat($orderId)
            ->findOrFail($orderId)
        ;

        return response()
            ->json([
                'success' => true,
                'record' => $order,
            ]);
    }
}
