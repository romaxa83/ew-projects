<?php

namespace WezomCms\Orders\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use WezomCms\Orders\Contracts\PaymentDriverInterface;

/**
 * \WezomCms\Orders\Models\OrderPaymentInformation
 *
 * @property int $id
 * @property array|null $payment_data
 * @property string $order_ids
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @see OrderPaymentInformation::orders()
 * @property-read Collection|Order[] $orders
 *
 * @method static Builder|OrderPaymentInformation newModelQuery()
 * @method static Builder|OrderPaymentInformation newQuery()
 * @method static Builder|OrderPaymentInformation query()
 * @method static Builder|OrderPaymentInformation whereCreatedAt($value)
 * @method static Builder|OrderPaymentInformation whereId($value)
 * @method static Builder|OrderPaymentInformation whereUpdatedAt($value)
 * @mixin Eloquent
 */
class OrderPaymentInformation extends Model
{
    use HasFactory;

    public const TABLE = 'order_payment_information';

    protected $table = self::TABLE;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'payment_data',
        'order_ids',
    ];

    protected $attributes = [
        'payment_data' => '[]',
    ];

    protected $casts = [
        'payment_data' => 'array',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'payment_information_id');
    }

    public function getPaymentDriver(): ?PaymentDriverInterface
    {
        return $this->orders->count()
            ? $this->orders->first()->payment->makeDriver()
            : null;
    }

    public function getPaymentDriverPayload(): array
    {
        $driver = $this->getPaymentDriver();

        return $driver
            ? $driver->getPaymentPayload()
            : [];
    }

    public function getTotalSum(): float
    {
        return $this->orders->sum(fn (Order $order) => $order->getSumForPay());
    }

    public function setPaymentDataFromRequest(Request $request): void
    {
        $this->payment_data = $request->only([
            'pg_payment_id',
            'pg_result',
            'pg_card_pan',
            'pg_salt',
            'pg_sig',
            'pg_payment_method',
            'pg_card_owner',
            'pg_card_brand',
            'pg_failure_code',
            'pg_failure_description',
        ]);

        $this->save();
    }

    public function setOrderIds(): self
    {
        $this->order_ids = $this->orders->implode('id', ', ');

        return $this;
    }

    public function setPaymentData(array $data): void
    {
        $this->payment_data = $data;

        $this->save();
    }
}
