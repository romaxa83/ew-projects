<?php

namespace App\Http\Controllers;

use App\Services\Requests\VAPI\Commands\Assistants\GetAssistants;
use App\Services\VAPI\VapiService;
use Illuminate\Http\{JsonResponse, Request};
use App\Models\Order;

/**
 * Manage Sizing.
 */
class SizingController extends Controller
{

    /**
     * Update order sizing.
     * @param Request $request
     * @return JsonResponse
     * @throws \Throwable
     */
    public function saveOrderSizing(Request $request): JsonResponse
    {
//        resolve(VapiService::class)->getAssistantList();

        $validatedData = $request->validate([
            'order_id' => 'required|numeric',
            'sizing_is_auto' => 'required|boolean',
            'auto_sizing_volume' => 'nullable|numeric',
            'auto_sizing_weight' => 'nullable|numeric',
            'sizing_volume' => 'nullable|numeric',
            'sizing_weight' => 'nullable|numeric',
        ]);

        $order = Order::findOrFail($validatedData['order_id']);

        $order->sizing_is_auto = $request->sizing_is_auto ? 1 : 0;

        $sizing_volume = !$order->sizing_is_auto ? $request->sizing_volume : $request->auto_sizing_volume;
        $order->sizing_volume = $sizing_volume > 0 ? $sizing_volume : null;

        $sizing_weight = !$order->sizing_is_auto ? $request->sizing_weight : $request->auto_sizing_weight;
        $order->sizing_weight = $sizing_weight > 0 ? $sizing_weight : null;

        if (!$order->isDirty()) {
            return response()
                ->json([
                    'success' => true,
                    'msg' => 'Without changes',
                    'record' => null
                ]);
        }

        $order->save();

        return response()
            ->json([
                'success' => true,
                'msg' => 'Order changed',
                'record' => Order::withInventoriesFormat($validatedData['order_id'])->findOrFail($validatedData['order_id'])
            ]);
    }

}
