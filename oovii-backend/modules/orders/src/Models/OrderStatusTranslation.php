<?php

namespace WezomCms\Orders\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * \WezomCms\Orders\Models\OrderStatusTranslation
 *
 * @property int $id
 * @property int $order_status_id
 * @property string|null $name
 * @property string|null $notification_text
 * @property string $locale
 * @method static Builder|OrderStatusTranslation newModelQuery()
 * @method static Builder|OrderStatusTranslation newQuery()
 * @method static Builder|OrderStatusTranslation query()
 * @method static Builder|OrderStatusTranslation whereId($value)
 * @method static Builder|OrderStatusTranslation whereLocale($value)
 * @method static Builder|OrderStatusTranslation whereName($value)
 * @method static Builder|OrderStatusTranslation whereOrderStatusId($value)
 * @mixin Eloquent
 */
class OrderStatusTranslation extends Model
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
    protected $fillable = ['name', 'notification_text'];
}
