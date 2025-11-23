<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Settings\EstimateParameters;
use App\Models\Settings\Interstate\ShuttlePrice;
use Illuminate\Http\{JsonResponse, Request};

/**
 * For old Android APP.
 * Read order data.
 */
class ExportController extends Controller
{
    /**
     * @var \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|null
     */
    private $order;

    public function export(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'secret_key' => 'required|in:nhk5GEeqbHrnq44QXGl9,sCxtUPeehD6iulY8zfwx',
            // nhk5GEeqbHrnq44QXGl9 - la, sCxtUPeehD6iulY8zfwx - h2h
            'id' => 'required|numeric',
        ]);

        $division_id = 1;
        if ($validated['secret_key'] === 'nhk5GEeqbHrnq44QXGl9') {
            $division_id = 2;
        }

        $order = Order::withInventoriesFormat($validated['id'])
            ->withWaypointsFormat()
            ->withWorksFormat()
            ->with([
                'manager:id,name,email',
                'manager.employee:id,auth_user_id',
                'manager.employee.phones' => function ($q) {
                    return $q
                        ->orderBy('is_primary', 'desc')
                        ->orderBy('sort', 'asc');
                },
                'manager.employee.emails' => function ($q) {
                    return $q
                        ->orderBy('is_primary', 'desc')
                        ->orderBy('sort', 'asc');
                },
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
                'materials',
                'estimate',
            ])
            ->where('division_id', $division_id)
            ->findOrFail($validated['id']);

        $order->load([
            'estimate.'.$order->estimate->type,
            'calculated' => function ($q) use ($order) {
                $q->where('estimate_type', $order->estimate->type);
            },
        ]);

        // Меняем ключ
        $order->setRelation('calculated', $order->calculated->keyBy('title'));
        $this->order = $order;

        return response()
            ->json([
                    'success' => 1,
                ] + $this->format());
    }

    private function format(): array
    {
        return [
            'data' => [
                [
                    'order' => [$this->formatOrder()],
                    'phones' => $this->order->client->phones->map(function ($item) {
                        return [
                            'phone' => $item->value,
                        ];
                    }),
                    'emails' => $this->order->client->emails->map(function ($item) {
                        return [
                            'email' => $item->value,
                        ];
                    }),
                    'add_services' => [],
                    'charges' => [],
                    'packings' => $this->formatPackings(),
                    'points' => $this->formatWaypoints(),
                    'services' => $this->formatWorks(),
                    'manager' => $this->formatManager(),
                ],
                'items' => $this->formatItems(),
            ],
        ];
    }

    private function formatManager(): array
    {
        $manager = explode(' ', ($this->order->manager->name ?? 'Manager'));
        $phone = '2137840373';
        $email = 'info@h2hmove.com';
        if ($this->order->user_id && $this->order->manager->employee) {
            $phone = $this->order->manager->employee->phones->first()->value ?? '2137840373';
            $email = $this->order->manager->employee->emails->first()->value ?? 'info@h2hmove.com';
        }

        return [
            'name' => $manager[0] ?? null,
            'l_name' => $manager[1] ?? null,
            'phone' => $phone,
            'email' => $email,
        ];
    }

    private function formatWorks()
    {
        $data = [];
        foreach ($this->order->works as $v) {
            $f_w = $v->workTypes->first();

            $data[] = [
                'id' => $v->id,
                'order_id' => $this->order->id,
                'service_id' => $v->id,
                'date_time' => trim("{$v->start_date} {$v->start_time}"),
                'time_type' => true,
                'time_empty' => empty($v->start_time),
                'name' => $v->workTypes->implode('title', ', '),
                'sign' => $f_w ? substr($f_w->title, 0, 1) : '',
                'default' => $v->workTypes->contains('work_type_id', 1),
                'deleted_at' => null,
            ];
        }

        return $data;
    }

    private function formatWaypoints(): array
    {
        $data = [];
        foreach ($this->order->waypoints as $k => $v) {
            $data[] = [
                'id' => $v->id,
                'order_id' => $this->order->id,
                'address' => $v->address,
                'city' => $v->city,
                'zip' => $v->zip,
                'ap' => $v->ap,
                'floor' => "",
                'elevator' => $v->has_elevator === 1,
                'weight' => ($k + 1),
                'lat' => (string) $v->lat,
                'lng' => (string) $v->lng,
                'state' => $v->state,
                'point_type' => $v->type === 'pickup' ? 'P' : 'D',
                'note' => $v->notes->implode('value', ', '),
                'elevator_time' => '',
            ];
        }


        return $data;
    }

    private function formatItems()
    {
        $data = [];

        foreach ($this->order->inventories as $group) {
            if ($group->is_section) {
                foreach ($group->children as $v) {
                    $data[$v->id] = [
                        'id' => $v->id,
                        'item_id' => (int) $v->item_id,
                        'item_name' => $v->title,
                        'item_weight' => (float) $v->weight,
                        'item_cuft' => (float) $v->volume,
                        'item_price' => (float) $v->price,
                        'item_qty' => $v->qty,
                        'item_group_name' => $group->title,
                    ];
                }
            } else {
                // no group
                $data[$group->id] = [
                    'id' => $group->id,
                    'item_id' => (int) $group->item_id,
                    'item_name' => $group->title,
                    'item_weight' => (float) $group->weight,
                    'item_cuft' => (float) $group->volume,
                    'item_price' => (float) $group->price,
                    'item_qty' => $group->qty,
                    'item_group_name' => '-',
                ];
            }
        }

        return $data;
    }

    private function formatPackings()
    {
        $data = [];

        foreach ($this->order->materials as $v) {
            $data[] = [
                'id' => $v->id,
                'order_id' => $this->order->id,
                'packing_id' => $v->material_id,
                'packing_name' => $v->title,
                'packing_price' => (float) $v->price,
                'packing_price_check' => 1, // TODO Не уверен, походу типа если эта услуга выбрана чтоль
                'packing_pack' => (float) $v->packing_price,
                'packing_pack_check' => $v->need_packing,
                'packing_unpack' => (float) $v->unpacking_price,
                'packing_unpack_check' => $v->need_unpacking,
                'packing_qty' => $v->qty,
            ];
        }

        return $data;
    }

    private function formatOrder()
    {

        $travel_fee = '';
        if ($this->order->estimate->travel_fee) {
            $travel_fee = $this->order->estimate->fee_type === 'sum' ?
                '$'.$this->order->estimate->travel_fee :
                $this->order->estimate->travel_fee.'%';
        }

        $interstate_mode = 0;
        $type = 'loc';
        if ($this->order->estimate->type === 'interstate') {
            $type = 'interstate';

            $interstate_mode = $this->order->estimate->interstate->estimate_rate === 'consolidated' ? 1 : 2;
        } elseif ($this->order->estimate->type === 'intrastate') {
            $type = 'state';
        }

        $f_wp = $this->order->waypoints->first();
        $f_work = $this->order->works->whereNotNull('start_date')->whereNotNull('start_time')->first();

        $multiple_dates = $this->order->works->unique('start_date')->count() > 1;

        $local_min = '';
        $local_max = '';
        $local_price_per_hour = '';
        if ($this->order->estimate->type === 'local') {
            $local_min = $this->order->estimate->local->hours_min;
            $local_max = $this->order->estimate->local->hours_max;
            $local_price_per_hour = $this->order->estimate->local->rate;
        }

        $total_estimate = $this->order->calculated['moving']['value'] ?? null;

        $total_estimate_min = $total_estimate;
        $total_estimate_max = $total_estimate;
        if (strstr($total_estimate, ' - ')) {
            [$min, $max] = explode(' - ', $total_estimate);

            $total_estimate_min = trim($min);
            $total_estimate_max = trim($max);
        }

        $total_price = $this->order->calculated['total']['value'];
        $total_price_min = $total_price;
        $total_price_max = $total_price;
        if (strstr($total_estimate, ' - ')) {
            [$min, $max] = explode(' - ', $total_price);

            $total_price_min = trim($min);
            $total_price_max = trim($max);
        }


        $min_param = EstimateParameters::get(['name', 'value'])->pluck('value', 'name')->all();

        $int_shuttle = ShuttlePrice::get()
            ->transform(function ($item) {
                return [
                    'min' => $item->min,
                    'max' => $item->max,
                    'value' => (int) $item->price,
                ];
            });

        $order_min_param = [
            'hours' => (int) $min_param['min_hours'],
            'trvl_fee' => 0, // TODO для h2h было 0.5
            'unpack_service' => (float) $min_param['unpacking_service_coefficient'],
            'pack_service' => (float) $min_param['packing_service_coefficient'],
            'fuel_surcharge' => (int) $min_param['fuel_coefficient'],
            'stair_carry' => (int) $min_param['stairs_flight_price'],
            'elevator_charge' => (int) $min_param['elevator_charge'],
            'main_mode' => 1,
            'shuttle' => $int_shuttle,
        ];

        return [
            'id' => $this->order->id,
            'responsible_worker_id' => $this->order->user_id,
            'f_name' => $this->order->client->name,
            'l_name' => $this->order->client->lname,
            'order_status' => $this->order->status_id,
            'order_source' => $this->order->source_id,
//            'order_confirm' => $this->order->estimate->dispatch_allowed ? 'booked' : 'book',
            'order_confirm' => 'book',
            'order_lost' => $this->order->status_id === 2,
            'order_closed' => in_array($this->order->status_id, [3, 5], true),
            'order_external' => false,
            'multiple_dates' => $multiple_dates,
            'move_type' => $type,
            'move_size' => $this->order->move_size_id,
            'building_type' => $f_wp->building_type_id ?? 0,
            'weight' => (string) $this->order->sizing_weight,
            'volume' => (string) $this->order->sizing_volume,
            'trucks' => $this->order->estimate->trucks,
            'crew' => $this->order->estimate->crews,
            'miles' => (string) $this->order->estimate->calculated_moving_distance,
            'trvl_fee' => $travel_fee,
            'min' => (string) $local_min,
            'max' => (string) $local_max,
            'price_per_hour' => (string) $local_price_per_hour,
            'fuel_exp' => '0',
            'claim' => '0',
            'referral' => '0',
            'total_estimate_min' => (string) $total_estimate_min, // TODO было без симола $
            'total_estimate_max' => (string) $total_estimate_max,
            'extra_total' => $this->order->calculated['materials']['value'] ?? null,
            'total_price_min' => $total_price_min,
            'total_price_max' => $total_price_max,
            'total_trvl_fee' => $this->order->calculated['fee']['value'] ?? null,
            'hash' => $this->order->hash,
            'price_per_hour_auto' => $this->order->estimate->type === 'local' && $this->order->estimate->local->is_auto === 1,
            'rate' => 'normal',
//         'intrastate_coef' => (string) "0",
            'intrastate_coef_auto' => $this->order->estimate->type === 'intrastate' && $this->order->estimate->intrastate->is_auto === 1,
//         'interstate_coef' => "0",
            'interstate_coef_auto' => $this->order->estimate->type === 'interstate' && $this->order->estimate->interstate->is_auto === 1,
            'weight_volume_auto' => (bool) $this->order->sizing_is_auto,
            'interstate_mode' => $interstate_mode,
//         'pick_up_shuttle' => false,
//         'delivery_shuttle' => false,
            'shuttle_price' => $this->order->calculated['shuttle']['value'] ?? null,
            'elevator_charge' => $this->order->calculated['elevators']['value'] ?? null,
            'stair_carry' => $this->order->calculated['floors']['value'] ?? null,
            'fuel_surcharge' => $this->order->calculated['fuel']['value'] ?? null,
            'unpack_ser' => $this->order->calculated['unpacking']['value'] ?? null,
            'pack_ser' => $this->order->calculated['packing']['value'] ?? null,
            'interstate_par' => json_encode($order_min_param),
            'delivery_days' => $this->order->estimate->type === 'interstate' ? $this->order->estimate->interstate->delivery_days : null,
//         'labor_and_transportation' => "0",
//         'truck_time' => "0",
//         'pack_time' => 0,
            'truck_pack_time' => false,
            'booked_date' => $f_work ? ("{$f_work->start_date} {$f_work->start_time}") : '',
            'created_at' => $this->order->created_at,
            'updated_at' => $this->order->updated_at,
//         'discount' => 0,
//         'status_changed' => "2021-02-03 17:02:29"
        ];
    }

    public function fake()
    {
        echo '{"success":1,"data":{"0":{"order":[{"id":462182,"responsible_worker_id":1,"f_name":"Meghan","l_name":"L","order_status":21,"order_source":1,"order_confirm":"book","order_lost":true,"order_closed":false,"order_external":false,"multiple_dates":false,"move_type":"state","move_size":0,"building_type":0,"weight":"5000","volume":"0","trucks":1,"miles":"120.34","crew":2,"trvl_fee":"0","min":"3","max":"3","price_per_hour":"0","fuel_exp":"0","claim":"0","referral":"0","total_estimate_min":"1737.5","total_estimate_max":"1737.5","extra_total":"0","total_price_min":"1737.5","total_price_max":"1737.5","total_trvl_fee":"0","hash":"IzS7cpYtaKbTftgj","price_per_hour_auto":true,"rate":"normal","intrastate_coef":"34.75","intrastate_coef_auto":false,"interstate_coef":"34.75","interstate_coef_auto":true,"weight_volume_auto":false,"interstate_mode":1,"pick_up_shuttle":false,"delivery_shuttle":false,"shuttle_price":"0","elevator_charge":"0","stair_carry":"0","fuel_surcharge":"0","unpack_ser":"0","pack_ser":"0","interstate_par":"{\"hours\":\"3\",\"trvl_fee\":\"0\",\"unpack_service\":\"1\",\"fuel_surcharge\":\"15\",\"stair_carry\":\"75\",\"elevator_charge\":\"50\",\"main_mode\":\"1\",\"pack_service\":\"1.5\",\"shuttle\":[{\"min\":0,\"max\":100,\"value\":\"300\"},{\"min\":101,\"max\":200,\"value\":\"300\"},{\"min\":201,\"max\":300,\"value\":\"300\"},{\"min\":301,\"max\":400,\"value\":\"300\"},{\"min\":401,\"max\":500,\"value\":\"500\"},{\"min\":501,\"max\":600,\"value\":\"500\"},{\"min\":601,\"max\":700,\"value\":\"500\"},{\"min\":701,\"max\":800,\"value\":\"500\"},{\"min\":801,\"max\":900,\"value\":\"800\"},{\"min\":901,\"max\":1000,\"value\":\"800\"}]}","delivery_days":"","labor_and_transportation":"0","truck_time":"0","pack_time":0,"truck_pack_time":false,"booked_date":"","created_at":"2020-01-30 23:06:38","updated_at":"2020-02-01 08:39:54","discount":0,"status_changed":"2020-02-01 08:39:54"}],"phones":[],"emails":[{"email":"Meg17810@aol.com"}],"add_services":[],"charges":[],"packings":[],"points":[{"id":62,"order_id":462182,"address":"","city":"Los Angeles","zip":"90065","ap":"","floor":"","elevator":false,"weight":1,"lat":"34.10885","lng":"-118.2234229","state":"CA","point_type":"P","note":"","elevator_time":""},{"id":63,"order_id":462182,"address":"","city":"Poway","zip":"92064","ap":"","floor":"","elevator":false,"weight":2,"lat":"32.9799762","lng":"-117.0087877","state":"CA","point_type":"D","note":"","elevator_time":""}],"services":[{"id":1,"order_id":462182,"service_id":1,"date_time":"2020-02-01 06:00:00","time_type":false,"time_empty":true,"name":"Moving","sign":"M","default":true,"deleted_at":null}],"manager":{"name":"Lyuda","l_name":"K.","phone":"2137840373","email":"info@h2hmove.com"}},"items":{"151":{"id":151,"item_id":0,"item_name":"4 boxes","item_weight":0,"item_cuft":0,"item_price":0,"item_qty":0,"item_group_name":null},"145":{"id":145,"item_id":0,"item_name":"Mattress","item_weight":0,"item_cuft":0,"item_price":0,"item_qty":0,"item_group_name":null},"146":{"id":146,"item_id":0,"item_name":"Lightweight bookshelf","item_weight":0,"item_cuft":0,"item_price":0,"item_qty":0,"item_group_name":null},"147":{"id":147,"item_id":0,"item_name":"Wicker chair","item_weight":0,"item_cuft":0,"item_price":0,"item_qty":0,"item_group_name":null},"148":{"id":148,"item_id":0,"item_name":"Wicker bookshelf","item_weight":0,"item_cuft":0,"item_price":0,"item_qty":0,"item_group_name":null},"149":{"id":149,"item_id":0,"item_name":"Kitchen table","item_weight":0,"item_cuft":0,"item_price":0,"item_qty":0,"item_group_name":null},"150":{"id":150,"item_id":0,"item_name":"Small coffee table","item_weight":0,"item_cuft":0,"item_price":0,"item_qty":0,"item_group_name":null}}}}';
        exit;
    }
}
