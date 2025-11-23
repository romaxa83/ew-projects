<?php

namespace App\Models\Order;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * App\Models\Order\MobileEstimate
 *
 * @property int $order_id
 * @property array|null $estimate JSON
 * @property array|null $bol JSON
 * @property int|null $bol_signed_employee_id
 * @property int|null $estimate_signed_employee_id
 * @property string|null $waiver_custom_reason
 * @property \Illuminate\Support\Carbon|null $estimate_signed_at
 * @property \Illuminate\Support\Carbon|null $bol_signed_at
 * @property \Illuminate\Support\Carbon|null $inspection_origin_signed_at
 * @property \Illuminate\Support\Carbon|null $inspection_destination_signed_at
 * @property \Illuminate\Support\Carbon|null $waiver_failure_to_protect_property_signed_at
 * @property \Illuminate\Support\Carbon|null $waiver_oversized_object_handling_signed_at
 * @property \Illuminate\Support\Carbon|null $waiver_custom_reason_signed_at
 * @property array|null $waiver_client_name
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|MobileEstimate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MobileEstimate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|MobileEstimate query()
 * @method static \Illuminate\Database\Eloquent\Builder|MobileEstimate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MobileEstimate whereMiscs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MobileEstimate whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MobileEstimate whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MobileEstimate whereBol($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MobileEstimate whereBolSignedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MobileEstimate whereEstimate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|MobileEstimate whereEstimateSignedAt($value)
 * @mixin \Eloquent
 *
 * @see self::bolSignedEmployee()
 * @property Employee|BelongsTo bolSignedEmployee
 *
 * @see self::estimateSignedEmployee()
 * @property Employee|BelongsTo estimateSignedEmployee
 */
class MobileEstimate extends Model
{
    public const TABLE = 'orders_estimates_mobile';
    protected $table = self::TABLE;

    public const SIGNATURE_INSPECTION_ORIGIN_AT = 'inspection_signature_origin_at';
    public const SIGNATURE_INSPECTION_DESTINATION_AT = 'inspection_signature_destination_at';
    public const SIGNATURE_WAIVER_PROTECT_PROPERTY = 'waiver_signature_failure_to_protect_property';
    public const SIGNATURE_WAIVER_OVERSIZE = 'waiver_signature_oversized_object_handling';
    public const SIGNATURE_WAIVER_CUSTOM = 'waiver_signature_custom_reason';


    protected $primaryKey = 'order_id';

    protected $casts = [
        'estimate' => 'array',
        'bol' => 'array',
        'waiver_client_name' => 'array',
        'estimate_signed_at' => 'datetime',
        'bol_signed_at' => 'datetime',
        'inspection_origin_signed_at' => 'datetime',
        'inspection_destination_signed_at' => 'datetime',
        'waiver_failure_to_protect_property_signed_at' => 'datetime',
        'waiver_oversized_object_handling_signed_at' => 'datetime',
        'waiver_custom_reason_signed_at' => 'datetime',
    ];

    protected $fillable = [
        'order_id',
        'estimate',
        'bol',
        'estimate_signed_at',
        'bol_signed_at',
        'bol_signed_employee_id',
        'estimate_signed_employee_id',
        'inspection_origin_signed_at',
        'inspection_destination_signed_at',
        'waiver_failure_to_protect_property_signed_at',
        'waiver_oversized_object_handling_signed_at',
        'waiver_custom_reason_signed_at',
        'waiver_custom_reason',
        'waiver_client_name',
    ];

    public function bolSignedEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'bol_signed_employee_id');
    }

    public function estimateSignedEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'estimate_signed_employee_id');
    }

    public static function getFileNameWithExt(string $name): string
    {
        return match ($name) {
            self::SIGNATURE_INSPECTION_ORIGIN_AT => self::SIGNATURE_INSPECTION_ORIGIN_AT . '.png',
            self::SIGNATURE_INSPECTION_DESTINATION_AT => self::SIGNATURE_INSPECTION_DESTINATION_AT . '.png',
            self::SIGNATURE_WAIVER_PROTECT_PROPERTY => self::SIGNATURE_WAIVER_PROTECT_PROPERTY . '.png',
            self::SIGNATURE_WAIVER_OVERSIZE => self::SIGNATURE_WAIVER_OVERSIZE . '.png',
            self::SIGNATURE_WAIVER_CUSTOM => self::SIGNATURE_WAIVER_CUSTOM . '.png',
            default => throw new \Exception("Type [{$name}] not supported.")
        };
    }

    public static function getFileFolder(string $name, $orderId): string
    {
        return match ($name) {
            self::SIGNATURE_INSPECTION_ORIGIN_AT => 'signatures/' . $orderId . '/inspection/',
            self::SIGNATURE_INSPECTION_DESTINATION_AT => 'signatures/' . $orderId . '/inspection/',
            self::SIGNATURE_WAIVER_PROTECT_PROPERTY => 'signatures/' . $orderId . '/waiver/',
            self::SIGNATURE_WAIVER_OVERSIZE => 'signatures/' . $orderId . '/waiver/',
            self::SIGNATURE_WAIVER_CUSTOM => 'signatures/' . $orderId . '/waiver/',
            default => throw new \Exception("Type [{$name}] not supported.")
        };
    }

    public static function getPath(string $name, $orderId): string
    {
        return match ($name) {
            self::SIGNATURE_INSPECTION_ORIGIN_AT => self::getFileFolder($name, $orderId) . self::getFileNameWithExt($name),
            self::SIGNATURE_INSPECTION_DESTINATION_AT => self::getFileFolder($name, $orderId) . self::getFileNameWithExt($name),
            self::SIGNATURE_WAIVER_PROTECT_PROPERTY => self::getFileFolder($name, $orderId) . self::getFileNameWithExt($name),
            self::SIGNATURE_WAIVER_OVERSIZE => self::getFileFolder($name, $orderId) . self::getFileNameWithExt($name),
            self::SIGNATURE_WAIVER_CUSTOM => self::getFileFolder($name, $orderId) . self::getFileNameWithExt($name),
            default => throw new \Exception("Type [{$name}] not supported.")
        };
    }

    public function setWaiverClient(?string $clientName)
    {
        $this->waiver_client_name = [
            'property_block' => $clientName,
            'oversize_block' => $clientName,
            'custom_block' => $clientName,
        ];
        $this->save();
    }

    public function formatPrice($price, $zeroFallback = false): string
    {
        if ($price) {
            return '$'.$price;
        } else if ($zeroFallback) {
            return '$0';
        } else {
            return '';
        }
    }

    public function rateGroupEstimate($group, $key): ?float
    {
        $num1 = $this->estimate['local_move'][$group][$key]['hours'] ?? 0;
        $num2 = $this->estimate['local_move'][$group][$key]['rate'] ?? 0;

        if ($num1 && $num2) {
            return round($num1 * $num2, 2);
        }

        return null;
    }

    public function rateGroupBol($group, $key): ?float
    {
        $num1 = $this->bol['local_move'][$group][$key]['hours'] ?? 0;
        $num2 = $this->bol['local_move'][$group][$key]['rate'] ?? 0;

        if ($num1 && $num2) {
            return round($num1 * $num2, 2);
        }

        return null;
    }


    public function bolHasNewTeams()
    {
        return !empty($this->bolTeams()) && (isset ($this->bol['teams'][0]['payTime']));
    }

    public function bolHasOldTeams()
    {
        return !empty($this->bolTeams()) && !(isset ($this->bol['teams'][0]['payTime']));
    }

    public function bolTeams()
    {
        return $this->bol['teams'] ?? [];
    }

    public function bolPackingTimes()
    {
        return $this->bol['packing_times'] ?? [];
    }

    public function teamWorkingHrs($team)
    {
        return $team['payHours'] - ($team['freeHours'] ?? 0);
    }

    public function teamWorkingCharge($team)
    {
        return $this->teamWorkingHrs($team) * $team['rate'];
    }

    public function totalBolTeams()
    {
        $total = 0;
        if ($this->bolHasNewTeams()) {
            foreach ($this->bolTeams() as $team) {
                $total += $this->teamWorkingCharge($team);
            }
        } elseif ($this->bolHasOldTeams()) {
            foreach ($this->bolTeams() as $team) {
                $total += ($team['hours'] * $team['rate']);
            }
        }
        return $total;
    }

    public function totalBolPackingTimes()
    {
        $total = 0;
        foreach ($this->bolPackingTimes() as $time) {
            $total += $time['total'];
        }
        return $total;
    }

    public function bolHasStraightTimeRange()
    {
        return ($this->bolStraightTimeStart() !== null && $this->bolStraightEndTime() !== null);
    }

    public function bolStraightTimeStart()
    {
        $time = $this->bol['local_move']['men']['straight']['startTime'] ?? null;
        return $time ? $this->formatTime($time) : null;
    }

    public function bolStraightEndTime()
    {
        $time = $this->bol['local_move']['men']['straight']['endTime'] ?? null;
        return $time ? $this->formatTime($time) : null;
    }

    private function formatTime($time): string
    {
        return $time['0'] . ':' . $time['1'];
    }

    public function rateGroupTotalEstimate($group): float|int
    {
        $total = 0;
        if ($add = $this->rateGroupEstimate($group, 'straight')) {
            $total += $add;
        }
        if ($add = $this->rateGroupEstimate($group, 'overtime')) {
            $total += $add;
        }
        if ($add = $this->rateGroupEstimate($group, 'holiday')) {
            $total += $add;
        }

        return $total;
    }

    public function rateGroupTotalBol($group): float|int
    {
        $total = 0;
        if ($add = $this->rateGroupBol($group, 'straight')) {
            $total += $add;
        }
        if ($add = $this->rateGroupBol($group, 'overtime')) {
            $total += $add;
        }
        if ($add = $this->rateGroupBol($group, 'holiday')) {
            $total += $add;
        }

        return $total;
    }

    public function travelTimeTotalEstimate(): ?float
    {
        $num1 = $this->estimate['local_move']['travel_time']['hours'] ?? 0;
        $num2 = $this->estimate['local_move']['travel_time']['rate'] ?? 0;

        if ($num1 && $num2) {
            return round($num1 * $num2, 2);
        }

        return null;
    }

    public function travelTimeTotalBol(): ?float
    {
        $num1 = $this->bol['local_move']['travel_time']['hours'] ?? 0;
        $num2 = $this->bol['local_move']['travel_time']['rate'] ?? 0;

        if ($num1 && $num2) {
            return round($num1 * $num2, 2);
        }

        return null;
    }

    public function mileageTotalEstimate(): ?float
    {
        $num1 = $this->estimate['local_move']['mileage_charge']['miles'] ?? 0;
        $num2 = $this->estimate['local_move']['mileage_charge']['rate'] ?? 0;

        if ($num1 && $num2) {
            return round($num1 * $num2, 2);
        }

        return null;
    }

    public function mileageTotalBol(): ?float
    {
        $num1 = $this->bol['local_move']['mileage_charge']['miles'] ?? 0;
        $num2 = $this->bol['local_move']['mileage_charge']['rate'] ?? 0;

        if ($num1 && $num2) {
            return round($num1 * $num2, 2);
        }

        return null;
    }

    public function totalSumEstimate()
    {
        $total = 0;
        if ($add = $this->rateGroupTotalEstimate('men')) {
            $total += $add;
        }
        if ($add = $this->rateGroupTotalEstimate('trucks')) {
            $total += $add;
        }
        if ($add = $this->travelTimeTotalEstimate()) {
            $total += $add;
        }
        if ($add = $this->mileageTotalEstimate()) {
            $total += $add;
        }
        if ($add = ($this->estimate['local_move']['valuation_charge'] ?? 0)) {
            $total += $add;
        }
        if ($add = ($this->estimate['local_move']['other_charge'] ?? 0)) {
            $total += $add;
        }
        if ($add = $this->packingChargesEstimate()['total']) {
            $total += $add;
        }
        if ($add = $this->orderChargesEstimate()['total']) {
            $total += $add;
        }

        return $total;
    }


    public function localMovesBol()
    {
        $response = [
            'records' => [],
            'total' => 0
        ];
        foreach ($this->bol['local_moves'] as $v) {
            $v['start_time'] = $v['start_time']['0'] . ':' . $v['start_time']['1'];
            $v['end_time'] = $v['end_time']['0'] . ':' . $v['end_time']['1'];
            $v['charge'] = $v['hours'] && $v['rate'] ? round($v['hours'] * $v['rate'], 2) : null;
            $response['total'] += $v['charge'];
            $response['records'][] = $v;
        }
        $response['total'] += $this->mileageTotalBol() + $this->travelTimeTotalBol();
        $response['total'] += !empty($this->bol['local_move']['valuation_charge']) ? $this->bol['local_move']['valuation_charge'] : 0;
        $response['total'] += !empty($this->bol['local_move']['other_charge']) ? $this->bol['local_move']['other_charge'] : 0;
        return $response;
    }

    public function packingChargesEstimate(): array
    {
        $resp = [
            'records' => [],
            'total' => 0,
        ];
        foreach ($this->estimate['packing_charges'] as $v) {
            $sum = 0;
            $v['charge']['rate'] = $v['charge']['rate'] ? (float)$v['charge']['rate'] : null;
            $v['packing']['rate'] = $v['packing']['rate'] ? (float)$v['packing']['rate'] : null;
            $v['unpacking']['rate'] = $v['unpacking']['rate'] ? (float)$v['unpacking']['rate'] : null;

            $v['charge']['sum'] = $v['charge']['qty'] && $v['charge']['rate'] ?
                round($v['charge']['qty'] * $v['charge']['rate'], 2) : null;
            $v['packing']['sum'] = $v['packing']['qty'] && $v['packing']['rate'] ?
                round($v['packing']['qty'] * $v['packing']['rate'], 2) : null;
            $v['unpacking']['sum'] = $v['unpacking']['qty'] && $v['unpacking']['rate'] ?
                round($v['unpacking']['qty'] * $v['unpacking']['rate'], 2) : null;

            if ($v['charge']['sum']) {
                $sum += $v['charge']['sum'];
            }
            if ($v['packing']['sum']) {
                $sum += $v['packing']['sum'];
            }
            if ($v['unpacking']['sum']) {
                $sum += $v['unpacking']['sum'];
            }

            $v['sum'] = $sum;
            $resp['total'] += $sum;

            $resp['records'][] = $v;
        }

        return $resp;
    }

    public function packingChargesBol(): array
    {
        $resp = [
            'records' => [],
            'total' => 0,
            'containers' => 0,
            'packing' => 0,
            'unpacking' => 0,
        ];
        foreach ($this->bol['packing_charges'] as $v) {
            $sum = 0;
            $v['charge']['rate'] = $v['charge']['rate'] ? (float)$v['charge']['rate'] : null;
            $v['packing']['rate'] = $v['packing']['rate'] ? (float)$v['packing']['rate'] : null;
            $v['unpacking']['rate'] = $v['unpacking']['rate'] ? (float)$v['unpacking']['rate'] : null;

            $v['charge']['sum'] = $v['charge']['qty'] && $v['charge']['rate'] ?
                round($v['charge']['qty'] * $v['charge']['rate'], 2) : null;
            $v['packing']['sum'] = $v['packing']['qty'] && $v['packing']['rate'] ?
                round($v['packing']['qty'] * $v['packing']['rate'], 2) : null;
            $v['unpacking']['sum'] = $v['unpacking']['qty'] && $v['unpacking']['rate'] ?
                round($v['unpacking']['qty'] * $v['unpacking']['rate'], 2) : null;

            if ($v['charge']['sum']) {
                $sum += $v['charge']['sum'];
                $resp['containers'] += $v['charge']['sum'];
            }
            if ($v['packing']['sum']) {
                $sum += $v['packing']['sum'];
                $resp['packing'] += $v['packing']['sum'];
            }
            if ($v['unpacking']['sum']) {
                $sum += $v['unpacking']['sum'];
                $resp['unpacking'] += $v['unpacking']['sum'];
            }

            $v['sum'] = $sum;
            $resp['total'] += $sum;

            $resp['records'][] = $v;
        }

        return $resp;
    }

    public function orderChargesEstimate()
    {
        $total = 0;

        $order_charges = $this->estimate['order_charges'];

        $order_charges['extra_charge'] = $order_charges['extra_charge'] ?
            round($order_charges['extra_charge'], 2) : null;
        $order_charges['hoisting'] = $order_charges['hoisting'] ?
            round($order_charges['hoisting'], 2) : null;

        $order_charges['stair_carry']['sum'] = $order_charges['stair_carry']['origin'] && $order_charges['stair_carry']['destination'] ?
            round($order_charges['stair_carry']['origin'] + $order_charges['stair_carry']['destination'], 2) : null;

        $order_charges['extra_labor']['sum'] = $order_charges['extra_labor']['hours'] && $order_charges['extra_labor']['rate'] ?
            round($order_charges['extra_labor']['hours'] * $order_charges['extra_labor']['rate'], 2) : null;

        $order_charges['other'] = [
            'records' => $order_charges['other'],
            'total' => collect($order_charges['other'])->sum('rate'),
        ];

        if ($order_charges['extra_charge']) {
            $total += $order_charges['extra_charge'];
        }
        if ($order_charges['hoisting']) {
            $total += $order_charges['hoisting'];
        }
        if ($order_charges['stair_carry']['sum']) {
            $total += $order_charges['stair_carry']['sum'];
        }
        if ($order_charges['bulky_item_charge']) {
            $total += $order_charges['bulky_item_charge'];
        }
        if ($order_charges['trip_transit_insurance']) {
            $total += $order_charges['trip_transit_insurance'];
        }
        if ($order_charges['extra_labor']['sum']) {
            $total += $order_charges['extra_labor']['sum'];
        }
        if ($order_charges['other']['total']) {
            $total += $order_charges['other']['total'];
        }
        $order_charges['total'] = $total;

        return $order_charges;
    }

    public function orderChargesBol()
    {
        $total = 0;

        $order_charges = $this->bol['order_charges'];

        $order_charges['extra_charge'] = $order_charges['extra_charge'] ?
            round($order_charges['extra_charge'], 2) : null;
        $order_charges['hoisting'] = $order_charges['hoisting'] ?
            round($order_charges['hoisting'], 2) : null;

        $stair_origin = (float)($order_charges['stair_carry']['origin'] ?? '');
        $stair_destination = (float)($order_charges['stair_carry']['destination'] ?? '');
        $order_charges['stair_carry']['sum'] =
            round($stair_origin + $stair_destination, 2);

        $order_charges['extra_labor']['sum'] = $order_charges['extra_labor']['hours'] && $order_charges['extra_labor']['rate'] ?
            round($order_charges['extra_labor']['hours'] * $order_charges['extra_labor']['rate'], 2) : null;

        $order_charges['other'] = [
            'records' => $order_charges['other'],
            'total' => collect($order_charges['other'])->sum('rate'),
        ];


        $order_charges['lowering']['sum'] = null;
        if (!empty($order_charges['lowering']['hours']) && !empty($order_charges['lowering']['rate'])) {
            $order_charges['lowering']['sum'] = round($order_charges['lowering']['hours'] * $order_charges['lowering']['rate'], 2);
            $total += $order_charges['lowering']['sum'];
        }

        if ($order_charges['extra_charge']) {
            $total += $order_charges['extra_charge'];
        }
        if ($order_charges['hoisting']) {
            $total += $order_charges['hoisting'];
        }
        if ($order_charges['stair_carry']['sum']) {
            $total += $order_charges['stair_carry']['sum'];
        }
        if ($order_charges['bulky_item_charge']) {
            $total += $order_charges['bulky_item_charge'];
        }
        if ($order_charges['trip_transit_insurance']) {
            $total += $order_charges['trip_transit_insurance'];
        }
        if ($order_charges['extra_labor']['sum']) {
            $total += $order_charges['extra_labor']['sum'];
        }
        $order_charges['total'] = $total;

        return $order_charges;
    }

    public function bolTotalChargesAboveServices()
    {
        $total = !empty($this->bol['finance']['total']) ? $this->bol['finance']['total'] : 0;
        return number_format(round($total, 2), 2);
    }

    public function bolBalanceDue()
    {
        $total = !empty($this->bol['finance']['balance_due']) ? $this->bol['finance']['balance_due'] : 0;
        return number_format(round($total, 2), 2);

    }

    public function bolPrepaid()
    {
        $total = !empty($this->bol['finance']['prepaid']) ? $this->bol['finance']['prepaid'] : 0;
        return number_format(round($total, 2), 2);

    }

    public function getBolPaid($key)
    {
        $paid = 0;
        if (!empty($this->bol['paid'][$key]))
            $paid = $this->bol['paid'][$key];
        return number_format(round($paid, 2), 2);
    }

    public function getBolWorkHours(): float
    {
        $result = 0;
        foreach ($this->bol['teams'] ?? [] as $item) {
            if(isset($item['payHours'])){
                $result += $item['payHours'];
            }
        }

        return $result;
    }

    public function getBolWorkDurations(): array
    {
        $start_date = null;
        $end_date = null;

        if(isset($this->bol['teams'])){
            $first_team = current($this->bol['teams']);
            $last_team = last($this->bol['teams']);

            if(
                isset($first_team['payTime']['start'])
                && is_array($first_team['payTime']['start'])
            ){
                $start_date = implode(':', $first_team['payTime']['start']);
            }

            if(
                isset($last_team['payTime']['end'])
                && is_array($last_team['payTime']['end'])
            ){
                $end_date = implode(':', $last_team['payTime']['end']);
            }

            $date = $this->bol_signed_at?->format('Y-m-d');

            if($start_date){
                $start_date = $date . ' ' . $start_date . ':00';
            }
            if($end_date){
                $end_date = $date . ' ' . $end_date . ':00';
            }
        }

        return [
            $start_date,
            $end_date,
        ];
    }
}
