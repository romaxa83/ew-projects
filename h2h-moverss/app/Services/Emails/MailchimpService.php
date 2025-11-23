<?php

namespace App\Services\Emails;

use App\Models\Order;
use App\Models\Settings\WaypointFlights;
use Carbon\Carbon;

class MailchimpService
{
    public function prepareMergeVars(Order $Order): array
    {
        $vars = [
            'CLIENT_NAME',
            'CLIENT_FIRST_NAME',
            'CLIENT_LAST_NAME',
            'CUSTOMER_PAGE_URL',
            'MANAGER_CURRENT_NAME',
            'MANAGER_NAME',
            'MANAGER_EMAIL',
            'ORDER_ID',
            'WAYPOINT_ORIGIN_FIRST_ADDRESS',
            'WAYPOINT_ORIGIN_ADDRESS',
            'WAYPOINT_ORIGIN_HAS_ELEVATOR',
            'WAYPOINT_PICKUP_ADDRESS',
            'WAYPOINT_DESTINATION_ADDRESS',
            'WAYPOINT_ORIGIN_STAIRS_FLIGHTS',
            'WAYPOINT_PICKUP_STAIRS_FLIGHTS',
            'WAYPOINT_DESTINATION_STAIRS_FLIGHTS',
            'WAYPOINT_DESTINATION_HAS_ELEVATOR',
            'MOVING_FIRST_DATETIME'
        ];
        $mergeVarsValue = $this->getMergeVarsValue($Order);

        $merge_vars = [];
        if (!empty($vars)) {
            foreach ($vars as &$var) {
                $var = strtoupper(trim($var));
                $merge_vars[] = [
                    'name' => $var,
                    'content' => !empty($mergeVarsValue[$var]) ? $mergeVarsValue[$var] : ''
                ];
            }
        }
        return $merge_vars;
    }

    private function getMergeVarsValue(Order $order): array
    {
        $works = [];
        $moving_dt = '';

        if ($order->works->isNotEmpty()) {
            $Works = $order->works->filter(function ($work) {
                if (!$work->workTypes->count())
                    return null;

                foreach ($work->workTypes as $type) {
                    if ($type->pivot && $type->pivot->work_type_id == 1) {
                        return true;
                    }
                }
                return null;
            });
            if ($Works->isNotEmpty()) {
                $Work = $Works->sortBy(function ($work) {
                    return (new Carbon($work->start_date . ' ' . $work->start_time))->getTimestamp();
                })->first();
                $moving_dt = (new Carbon($Work->start_date . ' ' . $Work->start_time))->format("M d, Y \a\\t g:i A");
            } else {
                $Work = $order->works->sortBy(function ($work) {
                    return (new Carbon($work->start_date . ' ' . $work->start_time))->getTimestamp();
                })->first();
                $moving_dt = (new Carbon($Work->start_date . ' ' . $Work->start_time))->format("M d, Y \a\\t g:i A");
            }

            foreach ($order->works as $work) {
                $WORK = ['TYPE' => '', 'DATE' => '', 'TIME' => ''];
                $WORK['TYPE'] = $work->workTypes->implode('title', ', ');
                // TODO
                $WORK['START'] = (new \DateTime($work->start_date))->format('M d, Y');  //'M d, Y
                $WORK['TIME'] = (new \DateTime($work->start_time))->format('g:i A'); //'g:i A'
                $works[] = $WORK;
            }
        }
//        $waypoints = [];
        $waypoints_origin_first_address = '';
        $waypoints_origin_first_zip = '';
        $waypoints_origin_address = '';
        $waypoints_origin_zip = '';
        $waypoints_origin_stairs_flights = '';
        $waypoints_origin_has_elevator = '';

        $waypoints_destination_address = '';
        $waypoints_destination_zip = '';
        $waypoints_destination_has_elevator = '';
        $waypoints_destination_stairs_flights = '';
        if ($order->waypoints) {
            if(
                $first_waypoint = $order
                    ->waypoints
                    ->where('type', 'pickup')
                    ->sortBy('created_at')
                    ->first()
            ){
                $waypoints_origin_first_address = $first_waypoint->address;
                $waypoints_origin_first_zip = $first_waypoint->zip;
            }

            foreach ($order->waypoints as $waypoint) {
                if ($waypoint->type == 'pickup') {
                    $waypoints_origin_address = $waypoint->address;
                    $waypoints_origin_zip = $waypoint->zip;
                    $waypoints_origin_has_elevator = $waypoint->has_elevator ? 'yes' : 'no';
                    $waypoints_origin_stairs_flights = $waypoint->flights_id;
                }
                if ($waypoint->type == 'destination') {
                    $waypoints_destination_address = $waypoint->address;
                    $waypoints_destination_zip = $waypoint->zip;
                    $waypoints_destination_has_elevator = $waypoint->has_elevator ? 'yes' : 'no';
                    $waypoints_destination_stairs_flights = $waypoint->flights_id;
                }
            }
        }
        if (!empty($waypoints_origin_stairs_flights)) {
            $WaypointFlight = WaypointFlights::find($waypoints_origin_stairs_flights);
            $waypoints_origin_stairs_flights = $WaypointFlight ? $WaypointFlight->title : '';
        } else {
            $waypoints_origin_stairs_flights = '';
        }
        if (!empty($waypoints_origin_zip) && preg_match('/(.*)( USA)$/', $waypoints_origin_address)) {
            $waypoints_origin_address = preg_replace('/(.*)( USA)$/', '$1 ' . $waypoints_origin_zip, $waypoints_origin_address);
        }
        if (!empty($waypoints_destination_zip) && preg_match('/(.*)( USA)$/', $waypoints_destination_address)) {
            $waypoints_destination_address = preg_replace('/(.*)( USA)$/', '$1 ' . $waypoints_destination_zip, $waypoints_destination_address);
        }
        if (!empty($waypoints_origin_first_zip) && preg_match('/(.*)( USA)$/', $waypoints_origin_first_address)) {
            $waypoints_origin_first_address = preg_replace('/(.*)( USA)$/', '$1 ' . $waypoints_origin_first_zip, $waypoints_origin_first_address);
        }

        return [
            'ORDER_ID' => $order->id,
            'CLIENT_NAME' => $order->client ? $order->client->name . ' ' . $order->client->lname : '',
            'CLIENT_FIRST_NAME' => $order->client ? $order->client->name : '',
            'CLIENT_LAST_NAME' => $order->client ? $order->client->lname : '',
            'CUSTOMER_PAGE_URL' => config('app.url') . '/customer/order/' . $order->hash,
            'MANAGER_CURRENT_NAME' => auth_user()?->employee?->full_name,
            'MANAGER_NAME' => $order->manager->name,
            'MANAGER_EMAIL' => $order->manager->email,
            'WAYPOINT_ORIGIN_FIRST_ADDRESS' => $waypoints_origin_first_address,
            'WAYPOINT_ORIGIN_ADDRESS' => $waypoints_origin_address,
            'WAYPOINT_PICKUP_ADDRESS' => $waypoints_origin_address,
            'WAYPOINT_DESTINATION_ADDRESS' => $waypoints_destination_address,
            'WAYPOINT_ORIGIN_STAIRS_FLIGHTS' => $waypoints_origin_stairs_flights,
            'WAYPOINT_PICKUP_STAIRS_FLIGHTS' => $waypoints_origin_stairs_flights,
            'WAYPOINT_DESTINATION_STAIRS_FLIGHTS' => $waypoints_destination_stairs_flights,
            'MOVING_FIRST_DATETIME' => $moving_dt,
            'WAYPOINT_ORIGIN_HAS_ELEVATOR' => $waypoints_origin_has_elevator,
            'WAYPOINT_DESTINATION_HAS_ELEVATOR' => $waypoints_destination_has_elevator
        ];
    }
}


