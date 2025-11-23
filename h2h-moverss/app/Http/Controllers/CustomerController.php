<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;


/**
 * The page with the order info that is sent to the client + on page can edit the products in the order.
 * @example Sample page: http://127.0.0.1:8000/customer/order/abr
 */
class CustomerController extends Controller
{
    /**
     * Page with order information and details.
     * @param string $hash Hash of order
     * @return Renderable
     */
    public function orderPublicView(string $hash): Renderable
    {
        $order = Order::whereHash($hash)
            ->withWorksFormat()
            ->withWaypointsFormat()
            ->withCount('works')
            ->with([
                'division',
                'manager:id,name,email',
                'manager.employee:id,auth_user_id',
                'manager.employee.phones',
                'client',
                'client.phones' => function ($q) {
                    return $q
                        ->select(['id', 'client_id', 'type_id', 'is_primary', 'value'])
                        ->orderBy('is_primary', 'desc')
                        ->orderBy('sort', 'asc');
                },
                'client.emails' => function ($q) {
                    return $q
                        ->select(['id', 'client_id', 'is_primary', 'value'])
                        ->orderBy('is_primary', 'desc')
                        ->orderBy('sort', 'asc');
                },
                'estimate:order_id,type,trucks,crews,calculated_moving_distance,calculated_moving_distance_auto,calculated_moving_distance_is_auto',
                'waypoints' => function ($q) {
                    return $q->orderBy('sort');
                },
                'waypoints.buildingType:id,title',
                'waypoints.parkingType:id,title',
                'waypoints.flights:id,title',
                'materials',
                'customsExtras',
            ])
            ->firstOrFail();

        $order->load([
            'estimate.' . $order->estimate->type,
            'calculated' => function ($q) use ($order) {
                $q->where('estimate_type', $order->estimate->type);
            },
            'afterwordText' => function ($q) use ($order) {
                $key = $order->estimate->type;
                if ($key === 'interstate') {
                    $key .= '_' . ($order->estimate->interstate->estimate_rate ?? null);
                }

                $q
                    ->whereDivisionId($order->division_id)
                    ->where('name', $key);
            }
        ]);

        // Changing key
        $order->setRelation('calculated', $order->calculated->keyBy('title'));

        $this->transformMaterials($order);
        $this->transformWorks($order);

        $estimate_type = $order->estimate->type;
        $order->is_estimate_available = isset($order->estimate->$estimate_type);

        return view('layouts.order.customer.orderPublicView.body', [
            'record' => $order,
        ]);
    }

    /**
     * Get Inventory. Rooms and their Objects.
     * @param string $hash Hash of order
     * @return JsonResponse
     */
    public function getInventories(string $hash): JsonResponse
    {
        $order_id = Order::whereHash($hash)->firstOrFail('id')->id;

        // Не допер как через load догрузить :(
        $order = Order::whereHash($hash)
            ->withInventoriesFormat($order_id)
            ->firstOrFail();


        $order->can_manage = true;

        return response()
            ->json([
                'success' => true,
                'order' => $order->only(['id', 'inventories', 'can_manage']),
                'types' => [
                    'rooms' => $this->orderRooms($order),
                ]
            ]);
    }

    /**
     * Load Rooms + Add from inventory.
     * @param $order Order
     * @return array
     */
    private function orderRooms(Order $order): array
    {
        $rooms = ['-'];

        $order->inventories
            ->each(function ($item) use (&$rooms) {
                if ($item->is_section && !in_array($item->title, $rooms, true)) {
                    $rooms[$item->id] = $item->title;
                }
            });

        return $rooms;
    }

    /**
     * Formatting output of Materials.
     * @param $order Order
     * @return void
     */
    private function transformMaterials(Order $order): void
    {
        $order->materials
            ->transform(function ($item) {
                $services = [];
                $price = $item->price;

                if ($item->need_packing) {
                    $price += $item->packing_price;
                    $services[] = 'Packing';
                }
                if ($item->need_unpacking) {
                    $price += $item->unpacking_price;
                    $services[] = 'Unpacking';
                }

                $item->services = implode(', ', $services);
                $item->total_price = $price * $item->qty;

                return $item;
            });
    }

    /**
     * Formatting output of Works.
     * @param $order Order
     * @return void
     */
    private function transformWorks(Order $order): void
    {
        $all_works = [];
        $order->works
            ->transform(function ($work) use (&$all_works) {

                $work->workTypes
                    ->each(function ($work_types) use (&$all_works) {
                        if (!in_array($work_types->title, $all_works, true)) {
                            $all_works[] = $work_types->title;
                        }
                    });

                $date = null;
                if ($work->start_date && $work->start_time && $work->start_time_to) {
                    $t_1 = Carbon::parse($work->start_time)->format('g:i A');
                    $t_2 = Carbon::parse($work->start_time_to)->format('g:i A');
                    $date = Carbon::parse($work->start_date)->format('m/d/Y');

                    $date .= ' between (' . $t_1 . ' - ' . $t_2 . ') Time Arrival Window';
                } elseif ($work->start_date && $work->start_time) {
                    $date = Carbon::parse($work->start_date . ' ' . $work->start_time)->format('m/d/Y \a\t g:i A') .
                        ' Time Arrival';
                } elseif ($work->start_date) {
                    $date = Carbon::parse($work->start_date)->format('m/d/Y');
                }

                $work->date = $date;

                return $work;
            });

        $order->works_all = $all_works;
    }

}
