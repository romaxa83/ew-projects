<?php

namespace App\Models;

use App\Events\OrderUpdated;
use App\Models\Order\CustomerPage;
use App\Models\Order\Payroll\Payroll;
use App\Models\Tasks\Task;
use App\Services\Communications\RecordCreateService;
use App\User;
use App\Utils\UpdateRelationsTrait;
use Auth;
use Carbon\Carbon;
use Database\Factories\Orders\OrderFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{
    BelongsToMany,
    HasMany,
    HasOne
};
use Illuminate\Http\Request;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\Collection;

/**
 * App\Models\Order
 *
 * @property int $id
 * @property int $client_id
 * @property int|null $user_id
 * @property int $division_id
 * @property int $status_id
 * @property int|null $source_id
 * @property int|null $move_size_id
 * @property int|null $sizing_is_auto
 * @property float|null $sizing_volume
 * @property float|null $sizing_weight
 * @property string|null $type
 * @property bool first_calc_as_client
 * @property int|null $base_id // если заказ с клонирован то здесь будет id базового заказа
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property int|null $updated_by
 * @property string|null reject_reason
 * @property string|null $hash md5: created_at+id
 * @property-read Collection|Order\Activity[] $activities
 * @property-read int|null $activities_count
 * @property-read CustomerPage|null $afterwordText
 * @property-read Collection|Audit[] $audits
 * @property-read int|null $audits_count
 * @property-read Collection|Order\Estimate\Calculated[] $calculated
 * @property-read int|null $calculated_count
 * @property-read Client|null $client
 * @property-read Collection|Order\CustomExtra[] $customsExtras
 * @property-read int|null $customs_extras_count
 * @property-read Division|null $division
 * @property-read Payroll|null $payroll
 * @property-read Order\Estimate|null $estimate
 * @property-read Order\Extended|null $extended
 * @property-read mixed $created_at_current_timezone
 * @property-read mixed $created_at_division_timezone
 * @property-read Collection|Order\Inventory[] $inventories
 * @property-read int|null $inventories_count
 * @property-read User|null $manager
 * @property-read Collection|Order\Material[] $materials
 * @property-read int|null $materials_count
 * @property-read Collection|Order\Notes[] $notes
 * @property-read int|null $notes_count
 * @property-read Collection|Order\Payment[] $payments
 * @property-read int|null $payments_count
 * @property-read Collection|Order\Notes[] $pinnedNotes
 * @property-read int|null $pinned_notes_count
 * @property-read Collection|Order\Service[] $services
 * @property-read int|null $services_count
 * @property-read Order\Status|null $status
 * @property-read Collection|Order\StatusChangeHistory[] $statusHistory
 * @property-read int|null $status_history_count
 * @property-read Order\StatusChangeHistory|null $statusHistoryLatest
 * @property-read Collection|Task[] $tasks
 * @property-read int|null $tasks_count
 * @property-read Collection|Task[] $tasksInwork
 * @property-read int|null $tasks_inwork_count
 * @property-read Collection|Task[] $tasksOverdue
 * @property-read int|null $tasks_overdue_count
 * @property-read Collection|Order\Waypoint[] $waypoints
 * @property-read int|null $waypoints_count
 * @property-read Collection|Order\Worker[] $workers
 * @property-read int|null $workers_count
 * @property-read Collection|Order\Work[] $works
 * @property-read int|null $works_count
 * @property-read Collection|Order\Tag[] $tags
 * @property-read int|null $tags_count
 * @property-read Order\MobileEstimate|null $mobileEstimate
 * @property-read Order\ForemanNote|[] $foremanNotes
 * @method static Builder|Order incomingLeads()
 * @method static Builder|Order mandrillTemplateVars()
 * @method static Builder|Order newModelQuery()
 * @method static Builder|Order newQuery()
 * @method static Builder|Order notInDispatch()
 * @method static Builder|Order query()
 * @method static Builder|Order statusLeadLost()
 * @method static Builder|Order statusLeadWon()
 * @method static Builder|Order unClosed()
 * @method static Builder|Order whereClientId($value)
 * @method static Builder|Order whereCreatedAt($value)
 * @method static Builder|Order whereDivisionId($value)
 * @method static Builder|Order whereHash($value)
 * @method static Builder|Order whereId($value)
 * @method static Builder|Order whereMoveSizeId($value)
 * @method static Builder|Order whereSizingIsAuto($value)
 * @method static Builder|Order whereSizingVolume($value)
 * @method static Builder|Order whereSizingWeight($value)
 * @method static Builder|Order whereSourceId($value)
 * @method static Builder|Order whereStatusId($value)
 * @method static Builder|Order whereType($value)
 * @method static Builder|Order whereUpdatedAt($value)
 * @method static Builder|Order whereUpdatedBy($value)
 * @method static Builder|Order whereUserId($value)
 * @method static Builder|Order withInventoriesFormat($order_id)
 * @method static Builder|Order withWaypointsFormat()
 * @method static Builder|Order withWorksFormat()
 * @method static OrderFactory factory(...$parameters)
 * @mixin \Eloquent
 */
class Order extends Model implements Auditable
{
    use AuditableTrait;
    use UpdateRelationsTrait;
    use HasFactory;

    public const MORPH_NAME = 'order';

    public const TABLE = 'orders';
    protected $table = self::TABLE;

    protected $dispatchesEvents = [
        'saving' => OrderUpdated::class,
    ];

    protected $fillable = [
        'first_calc_as_client',
    ];

    protected $casts = [
        'sizing_volume' => 'float',
        'sizing_weight' => 'float',
        'first_calc_as_client' => 'boolean',
    ];

    protected static function newFactory(): OrderFactory
    {
        return OrderFactory::new();
    }

    public function payroll()
    {
        return $this->hasOne(Payroll::class);
    }

    public function client(): HasOne
    {
        return $this->hasOne(Client::class, 'id', 'client_id');
    }

    public function manager(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function status(): HasOne
    {
        return $this->hasOne(Order\Status::class, 'id', 'status_id');
    }

    public function extended(): HasOne
    {
        return $this->hasOne(Order\Extended::class);
    }

    public function waypoints(): HasMany
    {
        return $this->hasMany(Order\Waypoint::class);
    }

    public function foremanNotes(): HasMany
    {
        return $this->hasMany(Order\ForemanNote::class);
    }

    public function workers(): HasMany
    {
        return $this->hasMany(Order\Worker::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function tasksOverdue(): HasMany
    {
        return $this->tasks()->overdue();
    }

    public function tasksInwork(): HasMany
    {
        return $this->tasks()->inwork();
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Order\Activity::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(Order\StatusChangeHistory::class, 'order_id', 'id');
    }

    public function statusHistoryLatest(): HasOne
    {
        return $this->hasOne(Order\StatusChangeHistory::class, 'order_id', 'id')->latestOfMany();
    }

    public function works(): HasMany
    {
        return $this->hasMany(Order\Work::class);
    }

    public function workLatest(): HasOne
    {
        return $this->hasOne(Order\Work::class, 'order_id')
            ->latestOfMany('start_date');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(Order\Notes::class);
    }

    public function pinnedNotes(): HasMany
    {
        return $this->notes()->whereIsPinned(1);
    }

    public function mobileEstimate(): HasOne
    {
        return $this->hasOne(Order\MobileEstimate::class);
    }

    public function estimate(): HasOne
    {
        return $this->hasOne(Order\Estimate::class);
    }

    public function calculated(): hasMany
    {
        return $this->hasMany(Order\Estimate\Calculated::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Order\Service::class);
    }

    public function materials(): HasMany
    {
        return $this->hasMany(Order\Material::class);
    }

    public function customsExtras(): HasMany
    {
        return $this->hasMany(Order\CustomExtra::class);
    }

    public function inventories(): HasMany
    {
        return $this->hasMany(Order\Inventory::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Order\Payment::class);
    }

    public function division(): HasOne
    {
        return $this->hasOne(Division::class, 'id', 'division_id');
    }

    public function afterwordText(): HasOne
    {
        return $this->hasOne(CustomerPage::class, 'division_id', 'division_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(
            Order\Tag::class,
            'orders_2_tags',
            'order_id',
            'tag_id'
        );
    }

    /**
     * test @see \Tests\Unit\Models\Orders\Order\RecountSizingAutoTest
     */
    public function recountSizingAuto(): void
    {
        $this->refresh();

        $weight = 0;
        $volume = 0;
        foreach ($this->inventories as $v) {
            if ($v->is_section) {
                continue;
            }

            if ($v->volume) {
                $volume += $v->volume * $v->qty;
            }
            if ($v->weight) {
                $weight += $v->weight * $v->qty;
            }
        }

        $this->sizing_volume = $volume;
        $this->sizing_weight = $weight;
        $this->save();
    }

    /**
     * Получить inventories в формате читаемом Nestable.
     * @param $query
     * @param int $oder_id
     * @return mixed
     * test @see \Tests\Unit\Models\Orders\Order\ScopeWithInventoriesFormatTest
     */
    public function scopeWithInventoriesFormat($query, int $oder_id)
    {
        return $query->with([
            'inventories' => function ($q) use ($oder_id) {
                return $q
                    ->with([
                        'children' => function ($q) use ($oder_id) {
                            $q->where('order_id', $oder_id);
                        },
                    ])
                    ->where('section_id', 0)
                    ->orderBy('is_section', 'desc')
                    ->orderBy('sort', 'asc');
            },
        ]);
    }

    /**
     * Получить Works + формат.
     * @param $query
     * @return mixed
     */
    public function scopeWithWorksFormat($query)
    {
        return $query->with([
            'works' => function ($q) {
                return $q
                    ->with([
                        'workTypes:title,orders_works_2_work.*',
                        'peakDate',
                        'dispatchTrucks:truck_id,work_id',
                        'dispatchTrucks.truck:id,title',
                        'dispatchEmployees:employer_id,work_id',
                        'dispatchEmployees.employee:id,name,l_name',
                    ])
                    ->withCount(['dispatchTrucks', 'dispatchEmployees'])
                    ->orderByRaw('(start_date IS NULL), start_date ASC');
            },
        ]);
    }

    /**
     * Получить Waypoints + формат.
     * @param $query
     * @return mixed
     */
    public function scopeWithWaypointsFormat($query)
    {
        return $query->with([
            'estimate:order_id,type,calculated_moving_time,calculated_moving_distance,calculated_moving_distance_auto,calculated_moving_distance_is_auto',
            'waypoints' => function ($q) {
                return $q
                    ->with(['notes', 'parkingType:id,title'])
                    ->orderBy('sort');
            },
        ]);
    }

    /**
     * Фильтрация статусов у которых запрещен диспатч.
     * @param $q
     * @return mixed
     */
    public function scopeNotInDispatch($q)
    {
        return $q->whereNotIn('status_id', [2, 7, 9, 19]);
    }

    public function scopeStatusLeadLost(Builder $q)
    {
        return $q->whereNotIn('status_id', [7, 9, 16]);
    }

    public function scopeStatusLeadWon(Builder $q)
    {
        return $q->whereNotIn('status_id', [5, 14, 15]);
    }

    public function scopeUnClosed(Builder $q)
    {
        return $q->whereNotIn('status_id', [9, 10]);
    }

    public function scopeIncomingLeads(Builder $q)
    {
        return $q->where('id', '>', 70000)
            ->whereHas('status', function ($q) {
                $q->where('group_id', 1);
            });
    }

    /**
     * test @see \Tests\Unit\Models\Orders\Order\ScopeMandrillTemplateVarsTest
    */
    public function scopeMandrillTemplateVars(Builder $q)
    {
        return $q->with([
            'manager:id,name,email',
            'manager.employee:id,name,l_name,auth_user_id',
            'manager.employee.emails' => function ($q) {
                return $q
                    ->select(['employee_id', 'value'])
                    ->orderBy('is_primary', 'desc')
                    ->orderBy('sort', 'asc');
            },
            'client:id,name,lname',
            'client.emails' => function ($q) {
                return $q
                    ->select(['client_id', 'value'])
                    ->orderBy('is_primary', 'desc')
                    ->orderBy('sort', 'asc');
            },
            'waypoints' => function ($q) {
                return $q->orderBy('sort', 'asc');
            },
            'works' => function ($q) {
                return $q->with([
                    'workTypes' => function ($q2) {
                        return $q2->orderBy('sort', 'ASC')->orderBy('title', 'ASC');
                    }
                ])
                    ->orderByRaw('(start_date IS NULL), start_date ASC');
            },
            'estimate:order_id,type,calculated_moving_min_value,calculated_moving_max_value',
        ]);
    }


    public function getOrdersDT(Request $request)
    {
        $Orders = $this->with([
            'manager:id,name',
            'statusHistory',
            'client:id,name,lname',
            'status:id,title,color',
            'tags' => function ($q) {
                return $q->orderBy('sort');
            },
            'client.phones' => function ($q) {
                return $q
                    ->select(['client_id', 'value'])
                    ->orderBy('is_primary', 'desc')
                    ->orderBy('sort', 'asc');
//                    ->take(1);
            },
            'client.emails' => function ($q) {
                return $q
                    ->select(['client_id', 'value'])
                    ->orderBy('is_primary', 'desc')
                    ->orderBy('sort', 'asc');
//                    ->take(1);
            },
            'client.tags' => function ($q) {
                return $q->orderBy('sort', 'asc');
            },
            'waypoints' => function ($q) {
                return $q->orderBy('sort', 'asc');
            },
            'waypoints.notes:waypoint_id,value',
            'calculated',
            'estimate:order_id,type,calculated_moving_min_value,calculated_moving_max_value',
            'estimate.local:order_id,hours_min,hours_max',
            'works' => function ($q) {
                return $q->with([
                    'workTypes' => function ($q2) {
                        return $q2->orderBy('sort', 'ASC')->orderBy('title', 'ASC');
                    }
                ])
                    ->orderByRaw('(start_date IS NULL), start_date ASC');
            },
            'division:id,short,title'
        ])
            ->withCount([
                'works', 'waypoints',
                'tasks' => function ($q) {
                    $q->where('status_id', 1);
                }
            ])
            ->when(true, function (Builder $query) use ($request) {
                // если выбран ID остальное игнорим
                if (!empty($request->id) ||
                    $request->filled('filters.order_id') ||
                    $request->filled('filters.client') ||
                    ($request->filled('filters.myLeads') && $request->filters['myLeads'] === 'on') ||
                    ($request->filled('filters.newLeads') && $request->filters['newLeads'] === 'on')) {

                    if (!empty($request->id)) {
                        $query->where('id', '=', $request->id);
                    }
                    if ($request->filled('filters.order_id')) {
                        $query->whereIn('id', $request->filters['order_id']);
                    }
                    if ($request->filled('filters.client')) {
                        $query->whereIn('client_id', $request->filters['client']);
                    }
                    // взаимоисключающие фильтры
                    if ($request->filled('filters.myLeads') && $request->filters['myLeads'] === 'on') {
                        $query->where('user_id', Auth::id());
                    }

                    if ($request->filled('filters.newLeads') && $request->filters['newLeads'] === 'on') {
                        $query->incomingLeads();
                    }
                } else {
                    // только если не выбран заказ вручную

                    // Find by Client Tag
                    if ($request->filled('filters.filter.clientTags')) {
                        $clients_ids = Client::query()
                            ->whereHas('tags',
                                fn($q) => $q->whereIn('tag_id', $request->filters['filter']['clientTags']))
                            ->get('id')
                            ->pluck('id')
                            ->all();

                        $query->whereIn('client_id', $clients_ids);
                    }

                    // Find by Order Tag
                    if ($request->filled('filters.filter.orderTags')) {
                        $query->whereHas('tags',
                            fn($q) => $q->whereIn('tag_id', $request->filters['filter']['orderTags']));
                    }

                    if ($request->filled('filters') && $request->filters['daterange-type'] === 'by-create-date') {
                        if ($request->filled('filters.stage')) {
                            $query->whereIn('status_id', $request->filters['stage']);
                        }
                        $divisionMiscs = session()->get('division.miscs');
                        $query->whereBetween('created_at', [
                            (new Carbon($request->filters['date-range']['start'], $divisionMiscs['tz']))->startOfDay()
                                ->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d H:i:s'),
                            (new Carbon($request->filters['date-range']['end'], $divisionMiscs['tz']))->endOfDay()
                                ->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d H:i:s')
                        ]);
                    } elseif ($request->filled('filters') && $request->filters['daterange-type'] === 'by-work-date') {
                        if ($request->filled('filters.stage')) {
                            $query->whereIn('status_id', $request->filters['stage']);
                        }
                        $query->whereHas('works', function ($q) use ($request) {
//                            $divisionMiscs = session()->get('division.miscs');
                            return $q->whereBetween('start_date', [
                                Carbon::parse($request->filters['date-range']['start'])->modify('00:00:00')->format('Y-m-d H:i:s'),
                                Carbon::parse($request->filters['date-range']['end'])->modify('23:59:59')->format('Y-m-d H:i:s')
                            ]);
                        });
                    } elseif ($request->filled('filters') && $request->filters['daterange-type'] === 'by-transition-date') {
                        $query->whereHas('statusHistory', function (Builder $q) use ($request) {
                            $divisionMiscs = session()->get('division.miscs');
                            $q->when($request->filled('filters.stage'), function ($q) use ($request) {
                                $q->whereIn('new_status', $request->filters['stage']);
                            })->whereBetween('created_at', [
                                (new Carbon($request->filters['date-range']['start'], $divisionMiscs['tz']))->startOfDay()
                                    ->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d H:i:s'),
                                (new Carbon($request->filters['date-range']['end'], $divisionMiscs['tz']))->endOfDay()
                                    ->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d H:i:s')
                            ]);
                        });
                    } elseif ($request->filled('filters') && $request->filters['daterange-type'] === 'by-none') {
                        if ($request->filled('filters.stage')) {
                            $query->whereIn('status_id', $request->filters['stage']);
                        }
                    }

                    if ($request->filled('filters.filter.manager')) {
                        $query->whereIn('user_id', $request->filters['filter']['manager']);
                    }
                    if ($request->filled('filters.filter.move-type')) {
                        $query->whereHas('estimate', function ($q) use ($request) {
                            return $q->whereIn('type', array_keys($request->filters['filter']['move-type']));
                        });
                    }
                    if ($request->filled('filters.filter.move-size')) {
                        $query->whereIn('move_size_id', array_keys($request->filters['filter']['move-size']));
                    }
                    if ($request->filled('filters.filter.source')) {
                        $query->whereIn('source_id', $request->filters['filter']['source']);
                    }
                    if ($request->filled('filters.filter.works')) {
                        $query->whereHas('works', function ($q) use ($request) {
                            return $q->whereHas('workTypes', function ($q2) use ($request) {
                                return $q2->whereIn('work_type_id', array_keys($request->filters['filter']['works']));
                            });
                        });
                    }
                    if ($request->filled('filters.filter.estimate.min') && $request->filled('filters.filter.estimate.max')) {
                        $query->whereHas('estimate', function ($q) use ($request) {
                            return $q->whereBetween('calculated_moving_min_value', [
                                (int)$request->filters['filter']['estimate']['min'],
                                (int)$request->filters['filter']['estimate']['max']
                            ])
                                ->orWhereBetween('calculated_moving_max_value', [
                                    (int)$request->filters['filter']['estimate']['min'],
                                    (int)$request->filters['filter']['estimate']['max']
                                ]);
                        });
                    }
                }

                // branch/division always!!
                if ($request->filled('filters.order_id') || $request->filled('filters.client')) {
                    $query->whereIn('division_id', $request->session()->get('division.allowed'));
                } else {
                    $query->where('division_id', $request->session()->get('division.id'));
                }

                if ($request->filled('filters.filter.tasks')) {
                    $query->when($request->filters['filter']['tasks'] === 'open', function ($q) {
                        $q->having('tasks_count', '>', 0);
                    }, function ($q) {
                        $q->having('tasks_count', '=', 0);
                    });
                }

                return $query;
            })
            ->orderBy('id', !empty($request->order[0]['dir']) ? $request->order[0]['dir'] : 'DESC');
        if ($request->id) {
            return $Orders->get();
        } else {
            return $Orders
                // TODO: проверить скормили ли верную страницу
                ->paginate($request->get('length'), ['*'], 'page', $request->start / $request->length + 1);
        }
    }

    /**
     * Добавить активити.
     * @param string $type Тип
     * @param array $miscs Данные которые менялись
     */
    public function addActivity($type, $miscs)
    {
        if ($this->id) {
            $act = new Order\Activity();
            $act->type = $type;
            $act->order_id = $this->id;
            $act->user_id = Auth::user()->id ?? 0;

            if (!empty($miscs['ext_id'])) {
                $act->ext_id = $miscs['ext_id'];
                unset($miscs['ext_id']);
            }

            $act->miscs = $miscs;

            $act->save();

            if($act->typeSupportCommunication()){
                RecordCreateService::handler($act);
            }

            return $act;
        }
        return null;
    }

    public function getMessagesActivity($limit = 10): array
    {
        $activity = new Order\Activity();
        $total = $activity->whereOrderId($this->id)->whereType('email')->count();
        $records = [];
        if ($total) {
            $records = $activity->whereOrderId($this->id)->whereType('email')
                ->when($limit, function ($q) use ($limit) {
                    return $q->take($limit)->latest();
                })
                ->get();
        }

        return [
            'total' => $total,
            'records' => $records,
        ];
    }

    /**
     * Обновить инфу о матеориалах.
     * @param $records
     * @return int
     */
    public function updateMaterials($records): int
    {
        return $this->updateRelations('materials', $records, 'material_id');
    }

    /**
     * Обновить Custom Extra.
     * @param $records
     * @return int
     */
    public function updateCustomExtra($records): int
    {
        return $this->updateRelations('customsExtras', $records);
    }

    public function getCreatedAtCurrentTimezoneAttribute()
    {
        $currentDivision = session('division');
        $Timezone = !empty($currentDivision['miscs']['tz']) ? $currentDivision['miscs']['tz'] : config('app.timezone');

        return (clone $this->created_at)->setTimezone($Timezone);
    }

    public function getCreatedAtDivisionTimezoneAttribute()
    {
        if ($Division = Division::find($this->division_id)) {
            $Timezone = !empty($Division->miscs['tz']) ? $Division->miscs['tz'] : config('app.timezone');
        } else {
            $currentDivision = session('division');
            $Timezone = !empty($currentDivision['miscs']['tz']) ? $currentDivision['miscs']['tz'] : config('app.timezone');
        }

        return (clone $this->created_at)->setTimezone($Timezone);
    }

}
