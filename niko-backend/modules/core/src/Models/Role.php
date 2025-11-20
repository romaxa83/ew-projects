<?php

namespace WezomCms\Core\Models;

use Illuminate\Database\Eloquent\Model;
use WezomCms\Core\Traits\Model\GetForSelectTrait;

/**
 * \WezomCms\Core\Models\Role
 *
 * @property int $id
 * @property string $name
 * @property array $permissions
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\WezomCms\Core\Models\Administrator[] $administrators
 * @property-read int|null $administrators_count
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Core\Models\Role newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Core\Models\Role newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Core\Models\Role query()
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Core\Models\Role whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Core\Models\Role whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Core\Models\Role whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Core\Models\Role wherePermissions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\WezomCms\Core\Models\Role whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Role extends Model
{
    use GetForSelectTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'permissions'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'permissions' => 'array',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function administrators()
    {
        return $this->belongsToMany(Administrator::class, 'role_administrators');
    }

    /**
     * @param  array  $permissions
     * @return bool
     */
    public function hasAccess(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  string  $permission
     * @return bool
     */
    private function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions);
    }
}
