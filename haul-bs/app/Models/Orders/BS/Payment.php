<?php

namespace App\Models\Orders\BS;

use App\Enums\Orders\PaymentMethod;
use App\Foundations\Models\BaseModel;
use Database\Factories\Orders\BS\PaymentFactory;
use Eloquent;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

/**
 * @property int id
 * @property int order_id
 * @property float amount
 * @property Carbon payment_date
 * @property PaymentMethod payment_method
 * @property string notes
 * @property string|null reference_number
 *
 * @mixin Eloquent
 *
 * @method static PaymentFactory factory(...$parameters)
 */
class Payment extends BaseModel
{
    use Filterable;
    use HasFactory;

    public $timestamps = false;

    public const TABLE = 'bs_order_payments';
    protected $table = self::TABLE;

    /**@var array<int, string>*/
    protected $fillable = [
        'amount',
        'payment_date',
        'payment_method',
        'notes',
        'order_id',
        'reference_number',
    ];

    protected $casts = [
        'payment_date' => 'datetime',
        'amount' => 'float',
        'payment_method' => PaymentMethod::class,
    ];
}
