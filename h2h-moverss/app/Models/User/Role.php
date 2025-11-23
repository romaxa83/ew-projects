<?php

namespace App\Models\User;

use Database\Factories\Users\RoleFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\User\Role
 *
 * @property int $id
 * @property string $title
 * @property boolean for_crew // роли которые в команде работников
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|Role newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Role newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Role query()
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Role whereUpdatedAt($value)
 * @method static Builder|Role orderManager()
 * @method static Builder|Role foreman()
 * @method static Builder|Role partner()
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User\RouteList[] $allowedRoutes
 * @property-read int|null $allowed_routes_count
 * @method static RoleFactory factory(...$parameters)
 * @mixin \Eloquent
 */
class Role extends Model
{
    use HasFactory;

    public const TABLE = 'users_roles';
    protected $table = self::TABLE;
    public const PARTNER = 'Partner';
    public const ACCOUNTANT = 'Accountant';
    public const ADMIN = 'Admin';
    public const MANAGER = 'Manager';
    public const DRIVERS = 'Drivers';
    public const FOREMAN = 'Foreman';

    public const FOREMAN_ID = 2;

    protected $fillable = [
        'for_crew'
    ];

    protected $casts = [
        'for_crew' => 'boolean',
    ];

    protected static function newFactory(): RoleFactory
    {
        return RoleFactory::new();
    }

    public function scopeOrderManager(Builder $q)
    {
        return $q->whereIn('role_id', [1, 5]);
    }

    public function scopeForeman(Builder $q)
    {
        return $q->whereIn('role_id', [2]);
    }

    public function scopePartner(Builder $q)
    {
        return $q->where('title', self::PARTNER);
    }

    public function allowedRoutes()
    {
        return $this->belongsToMany(RouteList::class, 'users_roles_2_routes', 'role_id', 'route_id');
    }

}
