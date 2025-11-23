<?php

namespace App\Models\Order;

use Database\Factories\Orders\WorkFactory;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\{Factories\HasFactory, SoftDeletes, Model};
use App\Models\{Audit, DispatchEmployer, DispatchTruck, Order, PeakDate, WorkTypes};
use Exception;

/**
 * App\Models\Order\Work
 *
 * @property int $id
 * @property int $order_id
 * @property string|null $start_date
 * @property string|null $start_time
 * @property string|null $start_time_to
 * @property string|null $duration
 * @property int|null $trucks
 * @property int|null $employees
 * @property string|null $notes
 * @property int|null $notes_by
 * @property \Illuminate\Support\Carbon|null $notes_created_at
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property int $in_dispatch Разрешен Dispatch
 * @property string|null $dispatch_updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection|DispatchEmployer[] $dispatchEmployees
 * @property-read int|null $dispatch_employees_count
 * @property-read \Illuminate\Database\Eloquent\Collection|DispatchTruck[] $dispatchTrucks
 * @property-read int|null $dispatch_trucks_count
 * @property-read Order|null $order
 * @property-read PeakDate|null $peakDate
 * @property-read \Illuminate\Database\Eloquent\Collection|WorkTypes[] $workTypes
 * @property-read int|null $work_types_count
 * @property-read int|null $audits_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @method static \Illuminate\Database\Eloquent\Builder|Work dispatch()
 * @method static \Illuminate\Database\Eloquent\Builder|Work estimateWorks()
 * @method static \Illuminate\Database\Eloquent\Builder|Work newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Work newQuery()
 * @method static \Illuminate\Database\Query\Builder|Work onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Work packingWorks()
 * @method static \Illuminate\Database\Eloquent\Builder|Work query()
 * @method static \Illuminate\Database\Eloquent\Builder|Work unpackingWorks()
 * @method static \Illuminate\Database\Eloquent\Builder|Work whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Work whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Work whereDispatchUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Work whereDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Work whereEmployees($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Work whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Work whereInDispatch($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Work whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Work whereNotesBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Work whereNotesCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Work whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Work whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Work whereStartTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Work whereStartTimeTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Work whereTrucks($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Work whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|Work withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Work withoutTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Work withTotalPayments()
 * @mixin \Eloquent
 * @method static WorkFactory factory(...$parameters)
 */
class Work extends Model implements Auditable
{
    use AuditableTrait;
    use SoftDeletes;
    use HasFactory;

    public const MORPH_NAME = 'order-work';

    public const TABLE = 'orders_works';
    protected $table = self::TABLE;

    protected $dates = [
        'deleted_at',
        'notes_created_at'
    ];

    protected $fillable = [
        'start_date',
        'duration',
        'trucks',
        'employees'
    ];

    protected static function newFactory(): WorkFactory
    {
        return WorkFactory::new();
    }

    public function order()
    {
        return $this->hasOne(Order::class, 'id', 'order_id');
    }

    public function workTypes()
    {
        return $this->belongsToMany(WorkTypes::class, 'orders_works_2_work', 'work_id', 'work_type_id');
    }

    public function peakDate()
    {
        return $this->hasOne(PeakDate::class, 'date', 'start_date');
    }

    public function dispatchTrucks()
    {
        return $this->hasMany(DispatchTruck::class, 'work_id', 'id');
    }

    public function dispatchEmployees()
    {
        return $this->hasMany(DispatchEmployer::class, 'work_id', 'id');
    }

    public function scopePackingWorks($query)
    {
        return $query->whereHas('workTypes', function ($q) {
            return $q->where('work_type_id', 2);
        });
    }

    public function scopeWithTotalPayments($q)
    {
        return $q->with([
            'order.payments' => function ($q) {
                $q
                    ->where('in_total_sum', 1)
                    ->select(['id', 'order_id', 'amount']);
            },
        ]);
    }

    public function scopeUnpackingWorks($query)
    {
        return $query->whereHas('workTypes', function ($q) {
            return $q->where('work_type_id', 8);
        });
    }

    public function scopeEstimateWorks($query)
    {
        return $query->whereHas('workTypes', function ($q) {
            return $q->where('work_type_id', 9);
        });
    }

    public function scopeDispatch($query)
    {
        return $query
            ->whereHas('order', function ($q) {
                $q->notInDispatch()
                    ->where('division_id', request()->session()->get('division.id'));
            })
            ->with([
                'order' => function ($q) {
                    return $q
                        ->with([
                            'client:id,name,lname',
                            'estimate:order_id,type,fee_type,travel_fee',
                            'waypoints' => function ($q) {
                                return $q->orderBy('sort');
                            },
                            'waypoints.flights'
                        ]);
                },
                'dispatchTrucks',
                'dispatchEmployees',
                'dispatchEmployees.employee:id,name,l_name',
                'workTypes:orders_works_2_work.*',
            ])
            ->where('in_dispatch', 1);
    }

    /**
     * Обновить связи.
     * @param string $relation Имя рилейшена
     * @param string $key Ключ данных
     * @param array $records Данные
     * @return int Было ли обновление в базе
     */
    public function updateDispatchRelations(string $relation, $key, $records)
    {
        $ids = [];
        $changed = 0;
        if (is_array($records)) {
            foreach ($records as $v) {
                if (empty($v[$key])) {
                    continue;
                }
//                throw new \Exception('test mode');
                $upd = $this->$relation()
                    ->updateOrCreate(
                        [
                            $key => $v[$key],
                        ],
                        $v);
                $ids[] = $upd->{$key};

                if (!$changed && ($upd->wasChanged() || $upd->wasRecentlyCreated)) {
                    $changed = 1;
                }
            }

            // Удаляем которые не в списке
            $for_delete = $this->$relation()->whereNotIn($key, $ids);
            if ($for_delete->count()) {
                $changed = 1;
                $for_delete->delete();
            }
        }

        return $changed;
    }

    /**
     * Включить диспатч для работы у которых есть дата.
     * @param int $order_id
     * @return bool
     * @throws \Exception
     */
    public function setAsDispatched(int $order_id): bool
    {
        $check_wo_date = self::query()
            ->where([
                ['order_id', $order_id],
                ['in_dispatch', 0]
            ])
            ->whereNull('start_date')
            ->first();
        if ($check_wo_date) {
            throw new Exception('The order has services w/o date');
        }

        $changed = false;
        self::query()
            ->where([
                ['order_id', $order_id],
                ['in_dispatch', 0]
            ])
            ->whereNotNull('start_date')
            ->get()
            ->each(function ($item) use (&$changed) {
                $item->in_dispatch = 1;

                if (!$item->start_time) {
                    $item->start_time = '09:00:00';
                    $item->notes_by = !$item->notes ? 0 : $item->notes_by;
                    $item->notes .= ' Automated time 9.00am for reschedule';
                    $item->notes_created_at = now();
                }

                $changed = true;
                $item->save();
            });

        return $changed;
    }

    public function setAsCancellation(int $order_id, bool $withException = true): bool
    {
        $changed = false;

        // При переводе на єтот статус- убирать show in dispatch для всех сервисов.
        // Но тут тоже нужна проверка - если есть назначенные траки или сотрудники -выбивать ошибку о том что сначала следует снять назначения.
        $records = self::query()
            ->where([
                ['order_id', $order_id],
                ['in_dispatch', 1]
            ])
            ->withCount([
                'dispatchTrucks',
                'dispatchEmployees',
            ])
            ->get();

        if($withException) {
            // Check if Service having assigned workers
            if ($records->sum('dispatch_trucks_count') || $records->sum('dispatch_employees_count')) {
                throw new Exception('The "Service" has an appointment of an employee in the dispatch. You have to detach an employee from dispatch.');
            }
        }

        $records
            ->each(function ($item) use (&$changed) {
                $changed = true;

                $item->in_dispatch = 0;
                $item->save();
            });

        return $changed;
    }
}
