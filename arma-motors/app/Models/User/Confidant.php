<?php

namespace App\Models\User;

use App\Casts\PhoneCast;
use App\Casts\UuidCast;
use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property string|null $uuid
 * @property int $car_id
 * @property string|null $name
 * @property string|null $phone
 */

class Confidant extends BaseModel
{
    use HasFactory;

    public $timestamps = false;

    public const TABLE_NAME = 'user_car_confidants';

    protected $table = self::TABLE_NAME;

    protected $casts = [
        'phone' => PhoneCast::class,
        'uuid' => UuidCast::class
    ];

    public function car(): HasOne
    {
        return $this->hasOne(Car::class);
    }
}


