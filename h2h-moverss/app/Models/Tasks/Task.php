<?php

namespace App\Models\Tasks;

use App\Models\Order;
use App\User;
use Auth;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\{Builder, Factories\HasFactory, Model, SoftDeletes};
use Database\Factories\Tasks\TaskFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * App\Models\Task
 *
 * @property int $id
 * @property int|null $type_id              // Группа
 * @property int $status_id                 // Статус
 * @property string $title                  // Название
 * @property string|null $description       // Описание
 * @property int $priority                  // Приоритет
 * @property \Illuminate\Support\Carbon|null $due_date  // Выполнить до
 * @property int $user_id                               // Автор
 * @property int|null $executor_id                      // Исполнитель
 * @property \Illuminate\Support\Carbon|null $notify_holder         // Уведомить исполнителя
 * @property \Illuminate\Support\Carbon|null $notify_subscribers    // Уведомить наблюдателей
 * @property mixed|null $miscs              // JSON
 * @property string|null $result            // Результат выполнения
 * @property string|null $due_time          // Время на когда выполнить
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property bool $is_read                  // просмотрена/прочитана таска
 * @property int|null $division_id
 * @property int|null $order_id
 * @property \Illuminate\Support\Carbon|null $result_at
 * @property-read User|null $author
 * @property-read User|null $executor
 * @property-read \Illuminate\Database\Eloquent\Collection|StatusHistory[] $history
 * @property-read int|null $history_count
 * @property-read Status|null $status
 * @property-read \Illuminate\Database\Eloquent\Collection|Subscriber[] $subscribers
 * @property-read int|null $subscribers_count
 * @property-read Type|null $type
 * @method static \Illuminate\Database\Eloquent\Builder|Task byDueDate()
 * @method static \Illuminate\Database\Eloquent\Builder|Task byOrder($order_id)
 * @method static \Illuminate\Database\Eloquent\Builder|Task bySubscriber($user_id = null)
 * @method static \Illuminate\Database\Eloquent\Builder|Task newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Task newQuery()
 * @method static \Illuminate\Database\Query\Builder|Task onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Task query()
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereDueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereExecutorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereMiscs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereNotifyHolder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereNotifySubscribers($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task wherePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereStatusId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereUserId($value)
 * @method static \Illuminate\Database\Query\Builder|Task withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Task withoutTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereDueTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task whereResult($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Task byFilters($filters)
 * @method static Builder|Task inwork()
 * @method static Builder|Task overdue()
 * @method static Builder|Task whereDivisionId($value)
 * @method static Builder|Task whereOrderId($value)
 * @method static Builder|Task whereResultAt($value)
 * @property-read \App\Models\Order|null $order
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read int|null $audits_count
 * @mixin \Eloquent
 *
 * @method static TaskFactory factory(...$parameters)
 */
class Task extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait;
    use SoftDeletes;

    public const MORPH_NAME = 'task';

    public const TABLE = 'tasks';
    protected $table = self::TABLE;

    protected $fillable = [
        'type_id',
        'title',
        'description',
        'priority',
        'executor_id',
        'result',
        'order_id',
        'division_id',
        'is_read'
    ];

    protected $dates = [
        'deleted_at',
        'due_date',
        'notify_holder',
        'notify_subscribers',
        'created_at',
        'result_at'
    ];

    protected $casts = [
        'miscs' => 'json',
        'is_read' => 'boolean',
    ];

    protected static function newFactory(): TaskFactory
    {
        return TaskFactory::new();
    }

    public static function boot()
    {
        parent::boot();
        static::updating(function ($item) {
            //Log::info('Updating event call: '.$item);
            if (!empty($item->result))
                $item->result_at = Carbon::now('UTC');
        });
    }


    public function scopeOverdue(Builder $q)
    {
        return $q->whereNull('result_at')->where('due_date', '<', Carbon::now('UTC'));
    }

    public function scopeInwork(Builder $q)
    {
        return $q->whereNull('result_at')->where('due_date', '>', Carbon::now('UTC'));
    }


    /**
     * @param $q
     * @param $filters
     * @return void
     */
    public function scopeByFilters($q, $filters)
    {
        $user_id = null;
        if ($filters['isInit']) {
            $user_id = $filters['user'] ?? Auth::id();
        }

        return $q
            ->when($user_id, function ($q, $user_id) {
                $q->where('executor_id', $user_id)
                    ->orWhere('user_id', $user_id);
            })
            ->where('status_id', $filters['status'])
            ->when(($filters['status'] === 1), function ($q) use ($filters) {
                $q->where('due_date', '<=', $filters['dateTo']);
            }, function ($q) use ($filters) {
                $q->whereBetween('due_date', [$filters['dateFrom'], $filters['dateTo']]);
            });
    }

    public function scopeByOrder($q, $order_id)
    {
        return $q
            ->with('status:id,title,class')
            ->where('order_id', $order_id);
//            ->whereJsonContains('miscs->relation->type', 'order')
//            ->whereJsonContains('miscs->relation->id', $order_id);
//            ->whereNotIn('status_id', [3, 4]) // NOT: Completed, Canceled
//            ->select(['id', 'title', 'due_date', 'user_id', 'status_id']);
    }

    public function scopeByDueDate($q)
    {
        return $q
            ->orderByRaw('ISNULL(due_date), due_date ASC')
            ->orderBy('id', 'desc');
    }

    public function author()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function status()
    {
        return $this->hasOne(Status::class, 'id', 'status_id');
    }

    public function type()
    {
        return $this->hasOne(Type::class, 'id', 'type_id');
    }

    public function executor()
    {
        return $this->hasOne(User::class, 'id', 'executor_id');
    }

    public function order()
    {
        return $this->hasOne(Order::class, 'id', 'order_id');
    }

    public function subscribers(): HasMany
    {
        return $this->hasMany(Subscriber::class, 'task_id', 'id');
    }

    public function history(): HasMany
    {
        return $this->hasMany(StatusHistory::class, 'task_id', 'id');
    }
}
