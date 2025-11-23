<?php

namespace WezomCms\Orders\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * \WezomCms\Orders\Models\DeliveryTranslation
 *
 * @property int $id
 * @property int $delivery_id
 * @property string|null $name
 * @property string $locale
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Orders\Models\DeliveryTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Orders\Models\DeliveryTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Orders\Models\DeliveryTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Orders\Models\DeliveryTranslation whereDeliveryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Orders\Models\DeliveryTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Orders\Models\DeliveryTranslation whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Orders\Models\DeliveryTranslation whereName($value)
 * @mixin \Eloquent
 */
class DeliveryTranslation extends Model
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
