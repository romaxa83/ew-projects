<?php

namespace App\Models\JD;

use App\ModelFilters\JD\DealerFilter;
use App\Models\BaseModel;
use App\Models\User\Nationality;
use App\Models\User\Role;
use App\Models\User\User;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property int $jd_id
 * @property string $jd_jd_id
 * @property string $name
 * @property string $country
 * @property boolean $status
 * @property string $created_at
 * @property string $updated_at
 * @property int $nationality_id
 *
 * @property-read Nationality|null $nationality
 * @property-read User[]|Collection $users
 * @property-read User[]|Collection users_ps
 * @property-read User[]|Collection $tm
 * @property-read User|null $sm
 *
 * @method static Builder|self query()
 */

class Dealer extends BaseModel
{
    use Filterable;

    const TABLE = 'jd_dealers';
    protected $table = self::TABLE;

    protected $fillable = [
        'status'
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function modelFilter()
    {
        return $this->provideFilter(DealerFilter::class);
    }

    public function scopeActive(Builder $query)
    {
        return $query->where('status', true);
    }

    public function users_ps(): HasMany
    {
        return $this->hasMany(User::class)
            ->whereHas('roles', function(Builder $query){
                $query->where('role', Role::ROLE_PS);
            });
    }

    public function tm(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function sm(): HasOne
    {
        return $this->hasOne(User::class, 'dealer_id', 'id');
    }

    public function nationality(): BelongsTo
    {
        return $this->belongsTo(Nationality::class, 'nationality_id', 'id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'dealer_user',
            'dealer_id', 'user_id'
        );
    }
}
