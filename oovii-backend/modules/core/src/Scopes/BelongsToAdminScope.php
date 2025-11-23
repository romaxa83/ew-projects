<?php

namespace WezomCms\Core\Scopes;

use Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use WezomCms\Core\Contracts\BelongsToAdminInterface;
use WezomCms\Core\Models\Administrator;

class BelongsToAdminScope implements Scope
{
    /**
     * @param  Builder  $builder
     * @param  Model  $model
     */
    public function apply(Builder $builder, Model $model)
    {
        if ($model instanceof BelongsToAdminInterface) {
            /** @var Administrator $user */
            $user = Auth::user();

            if ($user && $user instanceof Administrator && $user->onlyProvider()) {
                $builder->whereHas(
                    'administrator',
                    fn (Builder $query) => $query->where(Administrator::TABLE . '.id', $user->id)
                );
            }
        }
    }
}
