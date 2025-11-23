<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Projects\ChicagoAllymoversController;
use App\Http\Requests\Order\EstimateSaveRequest;
use App\Models\Audit;
use App\Models\Settings\EstimateParameters;
use Illuminate\Http\{JsonResponse, Request};
use App\Models\Order;
use DB, Exception;

/**
 * Running calculations and managing the move type
 */
class EstimateController extends Controller
{

    public static function getAllowedOptions($divisionID)
    {
        $options = [
            ['estimate_type' => 'local', 'name' => 'fee_type', 'value' => ['sum']],
            ['estimate_type' => 'interstate', 'name' => 'fee_type', 'value' => ['sum']],
            ['estimate_type' => 'intrastate', 'name' => 'fee_type', 'value' => ['sum']],
        ];
        // Chicago IL
        if ($divisionID == 1) {
            $options = [
                ['estimate_type' => 'local', 'name' => 'fee_type', 'value' => ['percent']],
                ['estimate_type' => 'interstate', 'name' => 'fee_type', 'value' => ['sum']],
                ['estimate_type' => 'intrastate', 'name' => 'fee_type', 'value' => []],
            ];
        }
        // Los Angeles CA
        if ($divisionID == 2) {
            $options = [
                ['estimate_type' => 'local', 'name' => 'fee_type', 'value' => ['sum', 'percent']],
                ['estimate_type' => 'interstate', 'name' => 'fee_type', 'value' => ['sum']],
                ['estimate_type' => 'intrastate', 'name' => 'fee_type', 'value' => []],
            ];
        }
        return $options;
    }


    public static function getFieldOptionValue($field, $options)
    {
        $options = array_filter($options, function ($item) use ($field) {
            return $item['name'] == $field;
        });
        if (!empty($options)) {
            return current($options)['value'];
        }

        return null;
    }

    /**
     * Change the move type and run calculations for Interstate.
     * @param Request $request
     * @return JsonResponse
     */
    public function saveType(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_id' => 'required|integer|exists:orders,id',
            'type' => 'required|in:' . implode(',', array_keys(config('app.moving_types'))),
            'estimate_rate' => 'required|in:hourly,by_weight,expedited,consolidated',
        ]);

        $order = Order::with('materials')
            ->findOrFail($validated['order_id']);

        $estimate = $order->estimate()->first();
        $min_params = EstimateParameters::selected($order->division_id)
            ->whereIn('estimate_type', ['any', $validated['type']])
            ->get(['name', 'value'])
            ->pluck('value', 'name')
            ->all();

        $defaultFee = self::getFieldOptionValue('fee_type', array_filter(self::getAllowedOptions($order->division_id), function ($item) use ($validated) {
            return $item['estimate_type'] == 'any' || $item['estimate_type'] == $validated['type'];
        }));

        $defaults = [
            'fee_type' => !empty($defaultFee) ? current($defaultFee) : 'sum'
        ];


        try {
            $estimate
                ->update([
                    'type' => $validated['type'],
                    'fee_type' => isset($min_params['fee_type']) ? $min_params['fee_type'] : $defaults['fee_type'],
                    'travel_fee' => isset($min_params['travel_fee']) ? $min_params['travel_fee'] : 0,
//                    'fee_type' => $validated['type'] !== 'local' ? 'sum' : $estimate->fee_type,
                ]);

            if ($validated['type'] === 'interstate') {
                $estimate
                    ->interstate()
                    ->updateOrCreate(
                        [
                            'order_id' => $validated['order_id'],
                        ],
                        [
                            'estimate_rate' => $validated['estimate_rate']
                        ]);
                $calculations = (new OrderCalculateController($order))->calculate($validated['type']);
                $estimate
                    ->interstate()
                    ->update([
                        'rate' => $calculations['rate'],
                        'rate_auto' => $calculations['rate_auto'],
                        'packing' => $calculations['packing'],
                        'unpacking' => $calculations['unpacking'],
                    ]);
            }

            $order->load('estimate.' . $validated['type']);
        } catch (Exception $e) {
            return response()
                ->json([
                    'success' => false,
                    'msg' => $e->getMessage() .
                        (app()->environment() !== 'production' ? ' File: ' . $e->getFile() . ' LINE: ' . $e->getLine() : '')
                ]);
        }

        return response()
            ->json([
                'success' => true,
                'estimate' => $order->estimate,
            ]);
    }

    public static function getCalculatedSorted($calculated)
    {
        $sortCalculated = config('app.calculated_table');
        return $calculated->sort(function ($a, $b) use ($sortCalculated) {
            $aSort = !empty($sortCalculated[$a->title]) ? $sortCalculated[$a->title]['sort'] : 0;
            $bSort = !empty($sortCalculated[$b->title]) ? $sortCalculated[$b->title]['sort'] : 0;
            return $aSort <=> $bSort;
        })->values();
    }


    /**
     * Save Estimate data for calculation.
     * @param EstimateSaveRequest $request
     * @return JsonResponse
     * @throws \Throwable
     */
    public function save(EstimateSaveRequest $request): JsonResponse
    {
        $validated = $request->validated();
//        dd($validated);
        /** @var $order Order */
        $order = Order::with('materials', 'customsExtras')->findOrFail($validated['order_id']);
        $estimate = $order->estimate()->first();
        $estimateBefore = $estimate ? clone $estimate : null;

        if ($estimateBefore)
            $estimateBefore->load($validated['type']);

        if ($estimate->is_locked && $validated['is_locked']) {
            return response()
                ->json([
                    'success' => true,
                    'is_locked' => true
                ]);
        }

        if ($validated['calculated_moving_distance_is_auto']) {
            $validated['calculated_moving_distance'] = $estimate->calculated_moving_distance_auto;
        }

        try {
            $calculateController = new OrderCalculateController($order);

            DB::beginTransaction();

            $estimate = $estimate
                ->updateOrCreate(
                    [
                        'order_id' => $validated['order_id'],
                    ],
                    $validated);

            $estimate
                ->{$validated['type']}()
                ->updateOrCreate(
                    [
                        'order_id' => $validated['order_id'],
                    ],
                    $validated[$validated['type']]);

//            dd($estimate);

            $calculations = $calculateController->calculate($validated['type']);

            if ($validated['type'] === 'local') {
                $estimate
                    ->local()
                    ->update([
                        'rate' => $calculations['rate'],
                        'rate_auto' => $calculations['rate_auto']
                    ]);
            } elseif ($validated['type'] === 'intrastate') {
                $estimate
                    ->intrastate()
                    ->update([
                        'rate' => $calculations['rate'],
                        'rate_auto' => $calculations['rate_auto']
                    ]);
            } elseif ($validated['type'] === 'interstate') {
                $estimate
                    ->interstate()
                    ->update([
                        'rate' => $calculations['rate'],
                        'rate_auto' => $calculations['rate_auto'],
                        'packing' => $calculations['packing'],
                        'unpacking' => $calculations['unpacking'],
                    ]);
            }
            $estimate->load($validated['type']);
            // Chicago trick. if were changed trucks, crews, local hours_min, local hours_max change all works to this
            $updatedServices = null;
            if ($order->division_id == 1)
                $updatedServices = (new ChicagoAllymoversController())->setOrder($order)->EstimatePostSave($estimateBefore, $estimate);

            $calculated = self::getCalculatedSorted($order->calculated()->{$validated['type']}()->get());

            if($order->first_calc_as_client){
                $order->update(['first_calc_as_client' => false]);

                Audit::query()
                    ->where('order_id', $order->id)
                    ->where('is_client_activity', false)
                    ->update([
                        'is_client_activity' => true,
                        'client_id' => $order->client_id
                    ]);
            }


            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();

            return response()
                ->json([
                    'success' => false,
                    'msg' => $e->getMessage() .
                        (app()->environment() !== 'production' ? ' File: ' . $e->getFile() . ' LINE: ' . $e->getLine() : '')
                ]);
        }

        return response()
            ->json([
                'success' => true,
                'estimate' => $estimate,
                'updatedServices' => $updatedServices,
                'calculated' => $calculated,
                'notices' => $calculateController->getNotices()
            ]);
    }
}
