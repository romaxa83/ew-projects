<?php

namespace App\Models\CashRegistry;

use App\Helpers\DbConnections;
use App\Models\Employee;
use Database\Factories\Orders\Payroll\PayrollFactory;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property positive-int id
 * @property positive-int employee_id
 * @property float cash_on_hand
 * @property bool active
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @see self::$employee()
 * @property Employee|BelongsTo $employee
 *
 * @see self::items()
 * @property CashRegistryItem[]|BelongsTo items
 *
 * @mixin \Eloquent
 */
class CashRegistry extends Model
{
    use Filterable;

    public const MORPH_NAME = 'cash-registry';

    public const TABLE = 'cash_registries';
    protected $table = self::TABLE;

    protected $connection = DbConnections::DEFAULT;

    protected $fillable = [
        'active'
    ];

    protected $dates = [];

    protected $casts = [
        'cash_on_hand' => 'float',
        'active' => 'bool',
    ];

    protected static function newFactory(): PayrollFactory
    {
        return PayrollFactory::new();
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(CashRegistryItem::class);
    }
}