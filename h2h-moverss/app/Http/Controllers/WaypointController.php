<?php

namespace App\Http\Controllers;

use App\Http\Requests\Order\WaypointRequest;
use App\Utils\USAStatesTrait;
use Illuminate\Http\{JsonResponse, Request};
use App\Models\Order;
use DB, Auth, Exception, Http;

/**
 * Manage Waypoint addresses.
 */
class WaypointController extends Controller
{
    use USAStatesTrait;

    /**
     * Get USA States.
     * @return JsonResponse
     */
    public function states(): JsonResponse
    {
        return response()
            ->json([
                'success' => true,
                'records' => $this->usa_states
            ]);
    }

    public function zipGeoInfo(Request $request): JsonResponse
    {
        return response()
            ->json([
                'success' => true,
                'result' => $this->getAddressInfo($request->q)
            ]);
    }

    /**
     * updateOrCreate a Waypoint.
     * @param  WaypointRequest  $request
     * @return JsonResponse
     * @throws \Throwable
     */
    public function save(WaypointRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $order = Order::withWaypointsFormat()->findOrFail($validated['order_id']);

        try {
            DB::beginTransaction();

            if (!$validated['sort']) {
                $validated['sort'] = $order->waypoints()->max('sort') + 1;
            }

            $wp = $order->waypoints()
                ->updateOrCreate(
                    [
                        'id' => $validated['id'] ?? null,
                    ],
                    $validated);

            $ids = [];
            if (!empty($validated['notes']) && is_array($validated['notes'])) {
                // Adding new comments
                foreach ($validated['notes'] as $v) {
                    $find = $wp->notes->where('id', $v['id'])->first();
                    if (!$find) {
                        $find = $wp->notes()
                            ->create([
                                'user_id' => Auth::user()->id,
                                'value' => $v['value'],
                            ]);
                    }
                    $ids[] = $find->id;
                }
            }

            // Removing deleted comments
            // $wp->notes()->whereNotIn('id', $ids)->delete();
            // удаляем таким способом чтоб залогировать удаление
            foreach ($wp->notes()->whereNotIn('id', $ids)->get() as $note) {
                $note->delete();
            }

            $this->recalculateDistance($order);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            return response()
                ->json([
                    'success' => false,
                    'msg' => $e->getMessage()
                ]);
        }

        // refresh и fresh has broken sorting
        $order = Order::withWaypointsFormat()->findOrFail($validated['order_id']);

        return response()
            ->json([
                'success' => true,
                'records' => $order->waypoints,
                'calculated_moving_time' => $order->estimate->calculated_moving_time,
                'calculated_moving_distance_auto' => $order->estimate->calculated_moving_distance_auto,
            ]);
    }


    /**
     * Remove a Waypoint.
     * @param  Request  $request
     * @return JsonResponse
     * @throws Exception
     */
    public function remove(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => 'required|integer|exists:orders_waypoints,id',
            'order_id' => 'required|integer|exists:orders,id',
        ]);

        $order = Order::with('waypoints')->findOrFail($validated['order_id']);

        $waypoint = $order->waypoints->where('id', $validated['id'])->first();

        if ($waypoint) {
            $waypoint->notes()->delete();
            $waypoint->delete();

            $this->recalculateDistance($order);
        }

        $order = Order::withWaypointsFormat()->findOrFail($validated['order_id']);

        return response()
            ->json([
                'success' => true,
                'records' => $order->waypoints,
                'calculated_moving_time' => $order->estimate->calculated_moving_time,
                'calculated_moving_distance_auto' => $order->estimate->calculated_moving_distance_auto,
            ]);
    }

    /**
     * Save Waypoints sorting.
     * @param  Request  $request
     * @return JsonResponse
     */
    public function saveSort(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_id' => 'required|integer|exists:orders,id',
            'records.*.id' => 'exists:orders_waypoints,id'
        ]);

        $sort = 1;
        foreach ($validated['records'] as $v) {
            $record = Order\Waypoint::find($v['id']);
            if ($record && $record->sort !== $sort) {
                $record->sort = $sort;
                $record->save();
            }
            $sort++;
        }

        return response()
            ->json([
                'success' => true,
            ]);
    }

    /**
     * Calculate the time and distance of the route.
     * And update in order.
     * @param  Order  $order
     */
    public function recalculateDistance(Order $order): void
    {
        $order->load([
            'waypoints' => function ($q) {
                return $q->orderBy('sort');
            }
        ]);

        $start = $order->waypoints->first();
        $end = $order->waypoints->last();

        if (
            (!$start || !$end) ||
            (!$start->lat || !$start->lng || !$end->lat || !$end->lng)) {
            return;
        }

        $params = [
            'origin' => "{$start->lat},{$start->lng}",
            'destination' => "{$end->lat},{$end->lng}",
        ];

        $wp_list = [];
        foreach ($order->waypoints as $v) {
            if ($v->id !== $start->id && $v->id !== $end->id) {
                $wp_list[] = "{$v->lat},{$v->lng}";
            }
        }

        if ($wp_list) {
            $params['waypoints'] = implode('|', $wp_list);
        }

        $response = $this->gMapsRequest('directions', $params);

        // Route recalculation if routes exists
        if ($response['routes']) {
            $total = [
                'distance' => 0,
                'duration' => 0,
            ];

            foreach ($response['routes'][0]['legs'] as $leg) {
                $total['distance'] += $leg['distance']['value']; // Meters
                $total['duration'] += $leg['duration']['value']; // Seconds
            }

            $total['distance'] /= 1000; // Meters to Km
            $total['distance'] = number_format(($total['distance'] * 0.62137), 2, '.', ''); // Km to Miles

            $update = [
                'calculated_moving_time' => $total['duration'],
                'calculated_moving_distance_auto' => $total['distance']
            ];
            if ($order->estimate->calculated_moving_distance_is_auto) {
                $update['calculated_moving_distance'] = $total['distance'];
            }
            $order->estimate()->update($update);
        }
    }

    /**
     * Request to Google Maps API.
     * @param  string  $section  Секция запроса
     * @param  array  $params  Параметры
     * @return array
     */
    public function gMapsRequest(string $section, array $params): array
    {
        $params = array_merge([
            'key' => config('app.google.maps.key'),
        ], $params);

        $url = 'https://maps.googleapis.com/maps/api/'.$section.'/json?'.http_build_query($params);

        return Http::get($url)
            ->json();
    }

    /**
     * Get data by address.
     * @param  string  $address
     * @return array
     */
    public function getAddressInfo(string $address): array
    {
        $response = $this->gMapsRequest('geocode', [
            'address' => $address,
            'components' => 'country:US'
        ]);

        $componentForm = [
            'street_number' => 'short_name',
            'route' => 'long_name',
            'locality' => 'long_name',
            'administrative_area_level_1' => 'short_name',
            'country' => 'long_name',
            'postal_code' => 'short_name',
        ];

        $res = [];
        if ($response['status'] === 'OK') {
            $address_data = [];
            foreach ($response['results'][0]['address_components'] as $v) {
                $type = $v['types'][0];

                if (isset($componentForm[$type])) {
                    $address_data[$type] = $v['short_name'];
                }
            }
            $response['results'][0]['address_data'] = $address_data;

            $res = $response['results'][0];
        }

        return $res;
    }
}
