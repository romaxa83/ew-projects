<?php

namespace WezomCms\Orders\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * \WezomCms\Orders\Models\DeliveryVariantTranslation
 *
 * @property int $id
 * @property int $delivery_variant_id
 * @property string|null $name
 * @property string|null $text
 * @property string $locale
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Orders\Models\DeliveryVariantTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Orders\Models\DeliveryVariantTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Orders\Models\DeliveryVariantTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Orders\Models\DeliveryVariantTranslation whereDeliveryVariantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Orders\Models\DeliveryVariantTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Orders\Models\DeliveryVariantTranslation whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Orders\Models\DeliveryVariantTranslation whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Orders\Models\DeliveryVariantTranslation whereText($value)
 * @mixin \Eloquent
 */
class DeliveryVariantTranslation extends Model
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
