<?php

namespace App\Models\Order;

use Database\Factories\Orders\PaymentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\PaymentAccount;
use Illuminate\Database\Eloquent\Relations\HasOne;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;


/**
 * App\Models\Order\Payment
 *
 * @property int $id
 * @property int $user_id
 * @property int|null $order_id
 * @property int $payment_account_id
 * @property string|null $description
 * @property string $amount
 * @property int $in_total_sum
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read PaymentAccount|null $account
 * @property-read \Illuminate\Database\Eloquent\Collection|\OwenIt\Auditing\Models\Audit[] $audits
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder|Payment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Payment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Payment query()
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereInTotalSum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment wherePaymentAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Payment whereUserId($value)
 * @mixin \Eloquent
 * @method static PaymentFactory factory(...$parameters)
 */
class Payment extends Model implements Auditable
{
    use AuditableTrait;
    use HasFactory;

    public const MORPH_NAME = 'order-payment';

    public const TABLE = 'orders_payments';
    protected $table = self::TABLE;

    protected $casts = [
        'in_total_sum' => 'boolean',
    ];

    protected static function newFactory(): PaymentFactory
    {
        return PaymentFactory::new();
    }

    public function account(): HasOne
    {
        return $this->hasOne(PaymentAccount::class, 'id', 'payment_account_id');
    }
}
