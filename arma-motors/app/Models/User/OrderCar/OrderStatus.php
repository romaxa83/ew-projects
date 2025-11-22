<?php

namespace App\Models\User\OrderCar;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property int $sort
 * @property bool $active
 * @property bool $for_front
 */

class OrderStatus extends BaseModel
{
    public $timestamps = false;

    public const TABLE_NAME = 'car_order_statuses';
    protected $table = self::TABLE_NAME;

    protected $casts = [
        'active' => 'bool',
        'for_front' => 'bool',
    ];

    public function isExitFromOrder(): bool
    {
        return $this->id === 9;
    }

    public function translations(): HasMany
    {
        return $this->hasMany(OrderStatusTranslation::class, 'model_id', 'id');
    }
    public function current(): HasOne
    {
        return $this->hasOne(OrderStatusTranslation::class,'model_id', 'id')->where('lang', \App::getLocale());
    }
}

