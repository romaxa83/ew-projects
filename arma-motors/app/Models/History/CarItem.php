<?php

namespace App\Models\History;

use App\Helpers\DateTime;
use App\Models\BaseModel;
use App\Models\User\Car;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $car_uuid
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read Invoice[]|Collection invoices
 * @property-read Order[]|Collection orders
 * @property-read Car|null car
 */
class CarItem extends BaseModel
{
    use HasFactory;

    public const TABLE = 'history_cars';
    protected $table = self::TABLE;

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class, 'car_uuid', 'uuid');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'row_id', 'id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'row_id', 'id');
    }

    public function scopeUserID(Builder $query, $id)
    {
        return $query->whereHas('car', function ($q) use ($id) {
            $q->where('user_id' , $id);
        });
    }

    public function scopeCarID(Builder $query, $id)
    {
        return $query->whereHas('car', function ($q) use ($id) {
            $q->where('id' , $id);
        });
    }

    public function scopeUserMobile(Builder $query)
    {
        if($user = \Auth::user()){
            return $query->whereHas('car', function ($q) use ($user) {
                $q->where('user_id' , $user->id);
            });
        }

        return $query;
    }
}
