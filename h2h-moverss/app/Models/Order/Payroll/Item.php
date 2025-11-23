<?php

namespace App\Models\Order\Payroll;

use App\Helpers\DbConnections;
use App\Models\Employee;
use App\Models\User\Role;
use Database\Factories\Orders\Payroll\ItemFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

/**
 * @property positive-int id
 * @property positive-int payroll_id
 * @property positive-int employee_id
 * @property positive-int role_id
 * @property float hourly_rate
 * @property float hours
 * @property float extras
 * @property boolean is_cc_due // оплата на карту для специалиста
 * @property Carbon created_at
 * @property Carbon updated_at
 *
 * @see self::role()
 * @property Role|BelongsTo role
 *
 * @see self::employee()
 * @property Employee|BelongsTo employee
 *
 * @see self::payroll()
 * @property Payroll|BelongsTo payroll
 *
 * @mixin \Eloquent
 * @method static ItemFactory factory(...$parameters)
 */
class Item extends Model implements Auditable
{
    use HasFactory;
    use AuditableTrait;

    public const MORPH_NAME = 'order-payroll-item';

    public const TABLE = 'order_payroll_items';
    protected $table = self::TABLE;

    protected $connection = DbConnections::DEFAULT;

    protected $fillable = [];

    protected $casts = [
        'is_cc_due' => 'boolean',
        'extras' => 'float',
        'hourly_rate' => 'float',
        'hours' => 'float',
    ];

    protected static function newFactory(): ItemFactory
    {
        return ItemFactory::new();
    }

    public function payroll(): BelongsTo
    {
        return $this->belongsTo(Payroll::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function getSubTotal(): float
    {
        return round($this->hourly_rate * $this->hours, 2);
    }

    public function getCashPaid(): float
    {
        if($this->is_cc_due) return 0;

        return round($this->getSubTotal() + $this->extras, 2);
    }

    public function getCCPaid(): float
    {
        if(!$this->is_cc_due) return 0;

        return round($this->getSubTotal() + $this->extras, 2);
    }
}