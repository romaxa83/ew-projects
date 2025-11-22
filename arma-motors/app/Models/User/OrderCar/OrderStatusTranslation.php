<?php

namespace App\Models\User\OrderCar;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property $model_id
 * @property string $lang
 * @property string $name
 *
 */

class OrderStatusTranslation extends Model
{
    public $timestamps = false;

    public const TABLE = 'car_order_status_translations';
    protected $table = self::TABLE;
}

