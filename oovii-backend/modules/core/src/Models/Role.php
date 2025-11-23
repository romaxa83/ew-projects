<?php

namespace WezomCms\Core\Models;

use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;
use WezomCms\Core\Traits\Model\GetForSelectTrait;

/**
 * \WezomCms\Core\Models\Role
 *
 * @property int $id
 * @property string $name
 * @property array $permissions
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection|Administrator[] $administrators
 * @property-read int|null $administrators_count
 * @method static Builder|Role newModelQuery()
 * @method static Builder|Role newQuery()
 * @method static Builder|Role query()
 * @method static Builder|Role whereCreatedAt($value)
 * @method static Builder|Role whereId($value)
 * @method static Builder|Role whereName($value)
 * @method static Builder|Role wherePermissions($value)
 * @method static Builder|Role whereUpdatedAt($value)
 * @mixin Eloquent
 */
class Role extends Model
{
    use GetForSelectTrait;
    use HasFactory;

    public const TABLE = 'roles';

    public const DEFAULT_PROVIDER = 'Provider';
    public const DEFAULT_MODERATOR = 'Moderator';

    protected $table = self::TABLE;

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

    public function administrators(): BelongsToMany
    {
        return $this->belongsToMany(Administrator::class, 'role_administrators');
    }

    /**
     * @param array $permissions
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
     * @param string $permission
     * @return bool
     */
    private function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->permissions, true);
    }

    public function isSystemRole(): bool
    {
        return in_array($this->name, [ self::DEFAULT_PROVIDER, self::DEFAULT_MODERATOR ]);
    }

    public function isProvider(): bool
    {
        return $this->name === self::DEFAULT_PROVIDER;
    }
}
