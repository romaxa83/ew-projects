<?php

namespace App\Http\Controllers;

use App\Models\Calculation\{
    LocalHourlyRates,
    IntrastateRates,
};
use App\Models\{Division,
    Order,
    PeakDate,
    Settings,
    Settings\EstimateParameters,
    Settings\Interstate\ShuttlePrice,
    Settings\Interstate\StateCoefficient
};
use Carbon\Carbon;
use Exception, NumberFormatter, DateTime, DateTimeZone;

/**
 * Shipping cost calculations.
 * Class OrderCalculateController
 * @package App\Http\Controllers
 */
class OrderCalculateController extends Controller
{

    private Order $order;
    private string $type;
    private NumberFormatter $CurrencyFormatter;
    private array $notices = [];
    private array $default = [
        'LocalEstimateSeason' => 'winter'
    ];

    public function __construct(Order $order)
    {
        $Division = Division::findOrFail($order->division_id);
        if (!empty($Division->miscs['local_rates_season']))
            $this->default['LocalEstimateSeason'] = $Division->miscs['local_rates_season'];

        $this->order = $order;
        $this->CurrencyFormatter = new NumberFormatter(config('app.formatter_currency_locale'),
            NumberFormatter::CURRENCY);
    }

    /**
     * Run calculation for Delivery Type.
     * @param string $type Delivery Type
     * @return array
     * @throws Exception
     */
    public function calculate(string $type): array
    {
        $this->type = $type;
        if ($type === 'local') {
            return $this->calculateLocal();
        } elseif ($type === 'intrastate') {
            return $this->calculateIntrastate();
        } elseif ($type === 'interstate') {
            return $this->calculateInterstate();
        }

        throw new Exception('FIXME OrderCalculateController line: ' . __LINE__);
    }

    /**
     * Local moving.
     * @return array
     * @throws Exception
     */
    private function calculateLocal(): array
    {
        $estimate = $this->order->estimate()->with('local')->first();

        $calculated = [
            'moving_min' => 0,
            'moving_max' => 0,
            'subtotal_min' => 0,
            'subtotal_max' => 0,
            'discount_min' => 0,
            'discount_max' => 0,
            'total_min' => 0,
            'total_max' => 0,
            'moving' => 0,
            'fee' => 0,
            'subtotal' => 0,
            'materials' => $this->calculateMaterials(),
            'discount' => 0,
            'paid' => $this->order->payments()->whereInTotalSum(1)->sum('amount'),
            'total' => 0,
            'left2pay_min' => 0,
            'left2pay_max' => 0,
        ];

        // Start date of work
        $start_date = $this->order->works()->orderBy('start_date')->first();
        $start_date = $start_date->start_date ?? null;

        try {
            $rate_auto = $this->getLocalHourlyAutoRate($estimate->crews, $start_date);
        } catch (Exception $e) {
            $this->notices[] = $e->getMessage();
            $rate_auto = 0;
        }
        if ($estimate->local->is_auto) {
            $rate = $rate_auto;
        } else {
            $rate = !empty(request()->local['rate']) ? $this->numberFormat(request()->local['rate']) : $rate_auto;
        }

        $calculated['total_min'] = $calculated['subtotal_min'] = $calculated['moving_min'] = $this->numberFormat(($rate * $estimate->local->hours_min));
        $calculated['total_max'] = $calculated['subtotal_max'] = $calculated['moving_max'] = $this->numberFormat(($rate * $estimate->local->hours_max));

        if ($calculated['materials']) {
            $calculated['total_min'] += $calculated['materials'];
            $calculated['total_max'] += $calculated['materials'];
        }

        // FEE. Percentage tip from hourly rate
        if (!empty($estimate->travel_fee)) {
            if ($estimate->fee_type === 'percent') {
                $calculated['fee'] = $this->numberFormat($rate * (float)$estimate->travel_fee);
            } elseif ($estimate->fee_type === 'sum') {
                $calculated['fee'] = $estimate->travel_fee;
            }
            $calculated['total_min'] += $calculated['fee'];
            $calculated['total_max'] += $calculated['fee'];
            $calculated['subtotal_min'] += $calculated['fee'];
            $calculated['subtotal_max'] += $calculated['fee'];
        }

        // Discount
        if (!empty($estimate->discount_value)) {
            if ($estimate->discount_type === 'percent') {
                $calculated['discount_min'] = $calculated['total_min'] - $calculated['total_min'] * (100 - $estimate->discount_value) / 100;
                $calculated['discount_max'] = $calculated['total_max'] - $calculated['total_max'] * (100 - $estimate->discount_value) / 100;
            } elseif ($estimate->discount_type === 'sum') {
                $calculated['discount_min'] = $calculated['discount_max'] = $estimate->discount_value;
            }
            $calculated['total_min'] -= $calculated['discount_min'];
            $calculated['total_max'] -= $calculated['discount_max'];
        }

        $calculated['left2pay_min'] = $calculated['total_min'] - $calculated['paid'];
        $calculated['left2pay_max'] = $calculated['total_max'] - $calculated['paid'];

        $min = min($calculated['left2pay_min'], $calculated['left2pay_max']);
        // Overpayment
        if ($min < 0) {
            $overpaid['min'] = $calculated['left2pay_min'] * -1; // remove minus by+
            $overpaid['max'] = $calculated['left2pay_max'] * -1; // remove minus by+
        }

        // Currency formatting
        foreach ($calculated as &$v) {
            $v = $this->formatCurrency($v);
        }
        unset($v);

        if (!empty($calculated['moving_min'])) {
            if ($calculated['moving_min'] !== $calculated['moving_max']) {
                $calculated['moving'] = $calculated['moving_min'] . ' - ' . $calculated['moving_max'];
                $calculated['subtotal'] = $calculated['subtotal_min'] . ' - ' . $calculated['subtotal_max'];
            } else {
                $calculated['moving'] = $calculated['moving_min'];
                $calculated['subtotal'] = $calculated['subtotal_min'];
            }
        }
        if (!empty($calculated['discount_min'])) {
            if ($calculated['discount_min'] !== $calculated['discount_max']) {
                $calculated['discount'] = $calculated['discount_min'] . ' - ' . $calculated['discount_max'];
            } else {
                $calculated['discount'] = $calculated['discount_min'];
            }
        }
        if (!empty($calculated['total_min'])) {
            if ($calculated['total_min'] !== $calculated['total_max']) {
                $calculated['total'] = $calculated['total_min'] . ' - ' . $calculated['total_max'];
                $calculated['left2pay'] = $calculated['left2pay_min'] . ' - ' . $calculated['left2pay_max'];
            } else {
                $calculated['total'] = $calculated['total_min'];
                $calculated['left2pay'] = $calculated['left2pay_min'];
            }
        }

        // if Overpayment
        if (isset($overpaid)) {
            $calculated['left2pay'] = $this->formatCurrency(0);
            if ($overpaid['min'] === $overpaid['max']) {
                $calculated['overpaid'] = $this->formatCurrency($overpaid['min']);
            } elseif ($overpaid['max'] < 0) {
                $calculated['overpaid'] = 'By min rate: ' . $this->formatCurrency($overpaid['min']);
            } else {
                $calculated['overpaid'] = $this->formatCurrency($overpaid['min']) . ' - ' . $this->formatCurrency($overpaid['max']);
            }
        } else {
            $this->order->calculated()->{$this->type}()
                ->where('title', 'overpaid')
                ->delete();
        }
        // /Currency formatting
//        dd($calculated);
        foreach ($calculated as $k => $v) {
            // skip some values
            if (strpos($k, '_min') !== false || strpos($k, '_max') !== false) {
                continue;
            }

            $this->order->calculated()->{$this->type}()
                ->updateOrCreate(
                    [
                        'order_id' => $this->order->id,
                        'estimate_type' => $this->type,
                        'title' => $k,
                    ],
                    [
                        'value' => $v
                    ]);
        }


        return [
            'rate' => $rate,
            'rate_auto' => $rate_auto,
        ];
    }

    /**
     * Intrastate moving.
     * @return array
     * @throws Exception
     */
    private function calculateIntrastate(): array
    {
        $estimate = $this->order->estimate()->with('intrastate')->first();
        $calculated = [
            'moving' => 0,
            'materials' => $this->calculateMaterials(),
            'discount' => 0,
            'fee' => 0,
            'total' => 0,
            'paid' => $this->order->payments()->whereInTotalSum(1)->sum('amount'),
        ];
        $coefficient = 0;
        if (empty($this->order->sizing_weight)) {
            $this->notices[] = 'Inventory weight value needed for Intrastate calculation!';
            $this->order->sizing_weight = 0;
        } else {
            try {
                $coefficient = (new IntrastateRates())
                    ->findRate($this->order->sizing_weight, $estimate->calculated_moving_distance, $this->order->division_id);
            } catch (Exception $e) {
                $this->notices[] = $e->getMessage();
            }
        }


//        $auto_rate = $this->numberFormat($coefficient * ($this->order->sizing_weight / 100));
        $auto_rate = $coefficient;
        if ($estimate->intrastate->is_auto) {
            $rate = $auto_rate;
        } else {
            $rate = !empty(request()->intrastate['rate']) ? request()->intrastate['rate'] : $auto_rate;
        }

        $calculated['moving'] = $calculated['total'] = $this->numberFormat(($rate * ($this->order->sizing_weight / 100)));

        if ($calculated['materials']) {
            $calculated['total'] += $calculated['materials'];
        }

        // FEE
        if (!empty($estimate->travel_fee) && $estimate->fee_type === 'sum') {
            $calculated['fee'] = $estimate->travel_fee;
            $calculated['total'] += $calculated['fee'];
        }

        // Discount
        if (!empty($estimate->discount_value)) {
            if ($estimate->discount_type === 'percent') {
                $calculated['discount'] = $calculated['total'] - $calculated['total'] * (100 - $estimate->discount_value) / 100;
            } elseif ($estimate->discount_type === 'sum') {
                $calculated['discount'] = $estimate->discount_value;
            }
            $calculated['total'] -= $calculated['discount'];
        }

        $calculated['left2pay'] = $calculated['total'] - $calculated['paid'];

        // Overpayment
        if ($calculated['left2pay'] < 0) {
            $calculated['overpaid'] = $calculated['left2pay'] * -1; // remove minus by+
            $calculated['left2pay'] = 0;
        }

        if (empty($calculated['overpaid'])) {
            $this->order->calculated()->{$this->type}()
                ->where('title', 'overpaid')
                ->delete();
        }


        // Currency formatting
        foreach ($calculated as &$v) {
            $v = $this->formatCurrency($v);
        }
        unset($v);


        foreach ($calculated as $k => $v) {
            $this->order->calculated()->{$this->type}()
                ->updateOrCreate(
                    [
                        'order_id' => $this->order->id,
                        'estimate_type' => $this->type,
                        'title' => $k,
                    ],
                    [
                        'value' => $v
                    ]);
        }

        return [
            'rate' => $rate,
            'rate_auto' => $auto_rate,
        ];
    }

    /**
     * Interstate moving.
     * @return array
     * @throws Exception
     */
    public function calculateInterstate(): array
    {
        $estimate = $this->order->estimate()->with('interstate')->first();

        $settings = EstimateParameters::selected($this->order->division_id)->interstate()
            ->get(['name', 'value'])->keyBy('name');

        $calculated = [
            'labor' => 0,
            'fuel' => 0,
            'elevators' => 0,
            'floors' => 0,
            'packing' => 0,
            'unpacking' => 0,
            'shuttle' => 0,
            'materials' => $this->calculateMaterials(),
            'discount' => 0,
            'fee' => 0,
            'total' => 0,
            'paid' => $this->order->payments()->whereInTotalSum(1)->sum('amount'),
        ];
        $response = [
            'packing' => 0,
            'unpacking' => 0,
            'rate_auto' => 0,
            'rate' => !empty(request()->interstate['rate']) ? +request()->interstate['rate'] : 0,
        ];
        if (empty($this->order->sizing_volume)) {
            $this->notices[] = 'Inventory volume value needed for Intrastate calculation!';
        }

        // Get auto coefficient if multiple states
        $routes = [];
        $byStates = $this->order->waypoints()->get()->keyBy('state');
        $wp_total = $byStates->count();
        if (!$wp_total) {
            $this->notices[] = 'Not enough waypoints for Interstate rate!';
        } elseif ($wp_total === 1) {
            $this->notices[] = 'Waypoints are located in one state!';
        }

//        dump($byStates->toArray());
        // Fetch waypoints, save all crossings
        $prev = false;
        foreach ($this->order->waypoints()->orderBy('sort')->get() as $waypoint) {
            if (!$prev) {
                $prev = $waypoint;
                continue;
            }

            if (!$waypoint->state || $waypoint->state === 'NA') {
                $this->notices[] = 'Not all waypoints having a correct State';
            }

            if (!(int)$waypoint->zip) {
                $this->notices[] = 'Not all waypoints having a correct ZIP';
            }

            if ($prev->state !== $waypoint->state) {
                $routes[] = [
                    'from' => $prev->state,
                    'to' => $waypoint->state
                ];
            }
            $prev = $waypoint;
        }

        if ($wp_total >= 2) {
            // Looking coefficients for all crossings
            try {
                foreach ($routes as &$route) {
                    if ($route['from'] !== $route['to']) {
                        $route['rate'] = +(new StateCoefficient())->findVolumeRate(
                            $this->order->sizing_volume,
                            $route['from'],
                            $route['to'],
                            $this->order->division_id
                        );
                    }
                }
                unset($route);
                // Get max rate
                $response['rate_auto'] = $routes ? max(array_column($routes, 'rate')) : 0;
            } catch (Exception $e) {
                $this->notices[] = $e->getMessage();
                $response['rate_auto'] = 0;
            }
        } else {
            $response['rate_auto'] = 0;
        }

        if (!empty(request()->interstate['is_auto'])) {
            $response['rate'] = $response['rate_auto'];
        }

        // Moving Calculation
        $calculated['labor'] = $response['rate'] * $this->order->sizing_volume;

        // Fuel Calculation
        $calculated['fuel'] = $this->numberFormat($response['rate'] * $this->order->sizing_volume * $settings['fuel_coefficient']->value / 100);

        // Packing & Unpacking
        if ($estimate->interstate->estimate_rate === 'consolidated') {
            if (!empty(request()->interstate['is_auto'])) {
                if (!empty($this->order->works()->packingWorks()->count())) {
                    $calculated['packing'] = $response['packing'] = $this->order->sizing_volume * $settings['packing_service_coefficient']->value;
                }
                if (!empty($this->order->works()->unpackingWorks()->count())) {
                    $calculated['unpacking'] = $response['unpacking'] = $this->order->sizing_volume * $settings['unpacking_service_coefficient']->value;
                }
            } else {
                $calculated['packing'] = $response['packing'] = $estimate->interstate->packing ? (float)$estimate->interstate->packing : 0;
                $calculated['unpacking'] = $response['unpacking'] = $estimate->interstate->unpacking ? (float)$estimate->interstate->unpacking : 0;
            }
        } elseif ($estimate->interstate->estimate_rate === 'expedited') {
            $calculated['packing'] = $response['packing'] = $estimate->interstate->packing ? (float)$estimate->interstate->packing : 0;
            $calculated['unpacking'] = $response['unpacking'] = $estimate->interstate->unpacking ? (float)$estimate->interstate->unpacking : 0;
        }

        // elevators charge
        $this->order->waypoints()->where('has_elevator', 1)
            ->each(function () use (&$calculated, $settings) {
                $calculated['elevators'] += $settings['elevator_charge']->value;
            });

        // floors/flights charge
        $this->order->waypoints()->where('has_elevator', 0)->where('flights_id', '>', 0)->with('flights')
            ->each(function ($record) use (&$calculated, $settings) {
                $calculated['floors'] += ($record->flights->value - 1) * $settings['stairs_flight_price']->value;
            });

        // Shuttles
        if ($this->order->sizing_volume) {
            $shuttlePrice = new ShuttlePrice();
            if ($estimate->interstate && $estimate->interstate->shuttle_pickup) {
                $calculated['shuttle'] += $shuttlePrice->getRate($this->order->sizing_volume, $this->order->division_id);
            }
            if ($estimate->interstate && $estimate->interstate->shuttle_delivery) {
                $calculated['shuttle'] += $shuttlePrice->getRate($this->order->sizing_volume, $this->order->division_id);
            }
        }

        // Add moving services. The discount comes only from them
        $calculated['total'] = $calculated['labor'] + $calculated['shuttle'] + $calculated['materials'];

        // FEE
        if (!empty($estimate->travel_fee) && $estimate->fee_type === 'sum') {
            $calculated['fee'] = $estimate->travel_fee;
            $calculated['total'] += $calculated['fee'];
        }

        // Discount
        if (!empty($estimate->discount_value)) {
            if ($estimate->discount_type === 'percent') {
                $calculated['discount'] = $calculated['total'] - $calculated['total'] * (100 - $estimate->discount_value) / 100;
            } elseif ($estimate->discount_type === 'sum') {
                $calculated['discount'] = $estimate->discount_value;
            }
            $calculated['total'] -= $calculated['discount'];
        }

        $calculated['total'] += (float)$calculated['fuel'] + $calculated['elevators'] + $calculated['floors'] + $calculated['packing'] + $calculated['unpacking'];
        $calculated['left2pay'] = $calculated['total'] - $calculated['paid'];

        // Overpayment
        if ($calculated['left2pay'] < 0) {
            $calculated['overpaid'] = $calculated['left2pay'] * -1; // remove minus by+
            $calculated['left2pay'] = 0;
        }

        if (empty($calculated['overpaid'])) {
            $this->order->calculated()->{$this->type}()
                ->where('title', 'overpaid')
                ->delete();
        }

        // Currency formatting
        foreach ($calculated as &$v) {
            $v = $this->formatCurrency($v);
        }
        unset($v);

        // Saving calculations
        foreach ($calculated as $k => $v) {
            $this->order->calculated()->{$this->type}()
                ->updateOrCreate(
                    [
                        'order_id' => $this->order->id,
                        'estimate_type' => $this->type,
                        'title' => $k,
                    ],
                    [
                        'value' => $v
                    ]);
        }

        return [
            'rate' => $response['rate'],
            'rate_auto' => $response['rate_auto'],
            'packing' => $response['packing'],
            'unpacking' => $response['unpacking'],
        ];
    }

    /**
     * Get cost of hour work.
     * @param integer $crews Number of employees
     * @param string $date Start date
     * @return float
     * @throws Exception
     */
    private function getLocalHourlyAutoRate(int $crews, $date): float
    {
        $peak = PeakDate::where('date', $date)->first();
        $peak_week_days = Settings::whereName('peak_week_days')->whereDivisionId(session('division')['id'])->first()->miscs;
        $currentSeason = $this->default['LocalEstimateSeason'];

        if (!$this->order->division_id) {
            throw new \Exception('Order has no valid division!');
        }
        $divisionInfo = Division::find($this->order->division_id);
        if (!$divisionInfo) {
            throw new \Exception("Unknown order division with id = {$this->order->division_id}!");
        }

        if ($date && $divisionInfo->miscs['local_rates_summer_from'] && $divisionInfo->miscs['local_rates_summer_to']) {
            $TZ = $divisionInfo->miscs['tz'] ? new DateTimeZone($divisionInfo->miscs['tz']) : new DateTimeZone();
            $StartDate = new DateTime($date, $TZ);
            $Y = $StartDate->format('Y');
            $from = $divisionInfo->miscs['local_rates_summer_from'];
            $to = $divisionInfo->miscs['local_rates_summer_to'];
            $SummerFrom = (new DateTime($Y . '-' . $from, $TZ))->modify('00:00:00');
            $SummerTo = (new DateTime($Y . '-' . $to, $TZ))->modify('23:59:59');
            if ($StartDate >= $SummerFrom && $StartDate <= $SummerTo)
                $currentSeason = 'summer';
        }

        $rate = LocalHourlyRates::where('crew_qty', $crews)->where('season', $currentSeason)->where('division_id',
            $this->order->division_id)->first();
        if (!$rate) {
//            throw new Exception('LocalHourlyRates crew_qty=' . $crews . ' was not found in local rate table');
            throw new Exception("Not found RatePerHour for estimate =Local, division = {$divisionInfo->title}, crew = {$crews}, season = {$currentSeason}!");
        }

        $day = Carbon::parse($date)->dayOfWeek;
        // periodic types
        if ($date && (in_array($day, $peak_week_days, true))) {
            $price_per_hour = $rate->peakday;
        } else {
            $price_per_hour = $rate->workday;
        }

        // day type is more priority than periodic type
        if ($peak && (int)$peak->type_id === 1) {
            // Holiday
            $price_per_hour = $rate->holiday;
        } elseif ($peak && (int)$peak->type_id === 2) {
            // Peak
            $price_per_hour = $rate->peakday;
        } elseif ($peak && (int)$peak->type_id === 3) {
            // WorkDay
            $price_per_hour = $rate->workday;
        }

        return (float)$this->numberFormat($price_per_hour);
    }


    /**
     * Get total price for materials.
     * @return float
     */
    public function calculateMaterials(): float
    {
        $materials = $this->order->materials
            ->reduce(function ($total, $item) {
                $sum = $item->price * $item->qty;
                if (!empty($item->need_packing)) {
                    $sum += $item->packing_price * $item->qty;
                }
                if (!empty($item->need_unpacking)) {
                    $sum += $item->unpacking_price * $item->qty;
                }

                return $total + $sum;
            }, 0);

        $materials += $this->order->customsExtras
            ->reduce(function ($total, $item) {
                return $total + $item->price;
            }, 0);

        return (float)$materials;
    }

    /**
     * Currency formatting from settings.
     * @param $value
     * @return string
     */
    public function formatCurrency($value): string
    {
        return preg_replace('/\.?0+$/', '',
            $this->CurrencyFormatter->formatCurrency($value, config('app.formatter_currency')));
    }

    /**
     * Get all notices.
     * @return array
     */
    public function getNotices(): array
    {
        return $this->notices;
    }

    /**
     * Format a number with grouped thousands.
     * @param float $number
     * @param int $decimals
     * @return string
     */
    public function numberFormat(float $number, int $decimals = 2): string
    {
        return number_format($number, $decimals, '.', '');
    }
}
