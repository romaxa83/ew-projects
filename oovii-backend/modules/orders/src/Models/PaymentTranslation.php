<?php

namespace WezomCms\Orders\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * \WezomCms\Orders\Models\PaymentTranslation
 *
 * @property int $id
 * @property int $payment_id
 * @property string|null $name
 * @property string $locale
 * @method static Builder|PaymentTranslation newModelQuery()
 * @method static Builder|PaymentTranslation newQuery()
 * @method static Builder|PaymentTranslation query()
 * @method static Builder|PaymentTranslation whereId($value)
 * @method static Builder|PaymentTranslation whereLocale($value)
 * @method static Builder|PaymentTranslation whereName($value)
 * @method static Builder|PaymentTranslation wherePaymentId($value)
 * @mixin Eloquent
 */
class PaymentTranslation extends Model
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name'];
}
