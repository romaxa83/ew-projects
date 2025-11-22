<?php

namespace App\Foundations\Modules\Permission\Repositories;

use App\Foundations\Enums\CacheKeyEnum;
use App\Foundations\Modules\Permission\Models\Role;
use App\Foundations\Modules\Permission\Roles\BaseRole;
use App\Foundations\Repositories\BaseEloquentRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

final readonly class RoleRepository extends BaseEloquentRepository
{
    protected function modelClass(): string
    {
        return Role::class;
    }

    public function getByBaseRole(BaseRole $baseRole): Model
    {
        return Role::query()
            ->where('name', $baseRole->getName())
            ->where('guard_name', $baseRole->getGuard())
            ->first();
    }

    public function list(array $select = ['*']): Collection
    {
        return Cache::tags(CacheKeyEnum::Roles->value)
            ->rememberForever(cache_key(CacheKeyEnum::Roles->value, $select),
                fn() => Role::query()
                    ->select($select)
                    ->orderBy('name')
                    ->get()
            );
    }
}
