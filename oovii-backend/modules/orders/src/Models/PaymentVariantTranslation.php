<?php

namespace WezomCms\Orders\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * \WezomCms\Orders\Models\PaymentVariantTranslation
 *
 * @property int $id
 * @property int $payment_variant_id
 * @property string|null $name
 * @property string|null $text
 * @property string $locale
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Orders\Models\PaymentVariantTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Orders\Models\PaymentVariantTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Orders\Models\PaymentVariantTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Orders\Models\PaymentVariantTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Orders\Models\PaymentVariantTranslation whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Orders\Models\PaymentVariantTranslation whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Orders\Models\PaymentVariantTranslation wherePaymentVariantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Orders\Models\PaymentVariantTranslation whereText($value)
 * @mixin \Eloquent
 */
class PaymentVariantTranslation extends Model
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
    protected $fillable = ['name', 'text'];
}
