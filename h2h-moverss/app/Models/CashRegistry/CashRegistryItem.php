<?php

namespace App\Models\CashRegistry;

use App\Enums\CashRegistry\OperationType;
use App\Helpers\DbConnections;
use App\ModelFilters\CashRegistry\CashRegistryItemFilter;
use App\Models\Employee;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Support\Carbon;

/**
 * @property positive-int id
 * @property positive-int|null executor_id
 * @property positive-int cash_registry_id
 * @property float sum
 * @property float balance
 * @property OperationType type
 * @property Carbon insert_date
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @see self::$executor()
 * @property Employee|BelongsTo $executor
 *
 * @see self::$cashRegistry()
 * @property CashRegistry|BelongsTo $cashRegistry
 *
 * @see self::$foreman()
 * @property Employee|HasOneThrough $foreman
 *
 * @mixin \Eloquent
 */
class CashRegistryItem extends Model
{
    use Filterable;

    public const MORPH_NAME = 'cash-registry-item';

    public const TABLE = 'cash_registry_items';
    protected $table = self::TABLE;

    protected $dates = [
        'insert_date'
    ];


    protected $connection = DbConnections::DEFAULT;

    protected $fillable = [
        'balance'
    ];

    protected $casts = [
        'sum' => 'float',
        'balance' => 'float',
        'type' => OperationType::class,
    ];

    public function modelFilter()
    {
        return $this->provideFilter(CashRegistryItemFilter::class);
    }

    public function executor(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'executor_id');
    }

    public function cashRegistry(): BelongsTo
    {
        return $this->belongsTo(CashRegistry::class, 'cash_registry_id');
    }

    public function foreman(): HasOneThrough
    {
        return $this->hasOneThrough(
            Employee::class,      // Модель конечной таблицы (Employee)
            CashRegistry::class, // Модель промежуточной таблицы (CashRegistry)
            'id',                            // Foreign key промежуточной таблицы в `CashRegistryItem` (локальный ключ на CashRegistry)
            'id',                            // Foreign key Employee в CashRegistry (локальный ключ на Employee)
            'cash_registry_id',              // Локальный ключ CashRegistryItem -> CashRegistry
            'employee_id'                    // Локальный ключ CashRegistry -> Employee
        );

    }
}
