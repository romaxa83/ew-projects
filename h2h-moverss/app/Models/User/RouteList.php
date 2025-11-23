<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\User\RouteList
 *
 * @property int $id
 * @property int $parent_id
 * @property int $is_group
 * @property string|null $method
 * @property string|null $uri
 * @property string|null $name
 * @property string|null $title
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User\Role[] $groups
 * @property-read int|null $groups_count
 * @method static \Illuminate\Database\Eloquent\Builder|RouteList newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RouteList newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|RouteList query()
 * @method static \Illuminate\Database\Eloquent\Builder|RouteList whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RouteList whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RouteList whereIsGroup($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RouteList whereMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RouteList whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RouteList whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RouteList whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RouteList whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|RouteList whereUri($value)
 * @mixin \Eloquent
 */
class RouteList extends Model
{
    protected $table = 'users_routes_list';
    protected $fillable = ['method', 'uri', 'name'];

    public function groups()
    {
        return $this->belongsToMany(Role::class, 'users_roles_2_routes', 'route_id', 'role_id');
    }
}
