<?php

namespace App\Models\Orders\Parts;

use Eloquent;
use App\Enums\Orders\Parts\PaymentMethod;
use App\Foundations\Models\BaseModel;
use Database\Factories\Orders\Parts\PaymentFactory;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;

/**
 * @property int id
 * @property int order_id
 * @property float amount
 * @property Carbon payment_at
 * @property PaymentMethod payment_method
 * @property string notes
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

    public const TABLE = 'parts_order_payments';
    protected $table = self::TABLE;

    /**@var array<int, string>*/
    protected $fillable = [
        'amount',
        'payment_at',
        'payment_method',
        'notes',
        'order_id',
    ];

    protected $casts = [
        'payment_at' => 'datetime',
        'amount' => 'float',
        'payment_method' => PaymentMethod::class,
    ];
}
