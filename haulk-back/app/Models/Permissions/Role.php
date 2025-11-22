<?php

namespace App\Models\Permissions;

use App\ModelFilters\Permissions\RoleFilter;
use App\Traits\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\Contracts\Role as RoleContract;

/**
 * @property int id
 * @property string name
 * @property string guard_name
 * @property string created_at
 * @property string updated_at
 *
 * @property Collection|Permission[] permissions
 *
 * @method static static|Builder query()
 * @method static static|Builder whereGuardName($guard)
 */
class Role extends \Spatie\Permission\Models\Role
{
    use Filterable;

    public const TABLE = 'roles';

    public function modelFilter(): string
    {
        return RoleFilter::class;
    }

    /**
     * @param string $name
     * @param $guardName
     * @return RoleContract
     * @noinspection PhpIncompatibleReturnTypeInspection
     */
    public static function findByName(string $name, $guardName = null): RoleContract
    {
        return Cache::rememberForever(
            "role:" . $name . ":" . ($guardName ?? "null"),
            static fn(): ?self => parent::findByName($name, $guardName ?? 'api')
        );
    }
}
