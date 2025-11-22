<?php


namespace App\Scopes\Saas\Support;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class SupportGlobalScope implements Scope
{

    /**
     * @inheritDoc
     */
    public function apply(Builder $builder, Model $model)
    {
        if (!auth()->check() || is_null($user = authUser())) {
            return;
        }
        if ($user->isBroker()) {
            $builder->whereHas(
                'user',
                function (Builder $builder) use($user) {
                    $builder->where('broker_id', $user->broker_id);
                }
            );
            return;
        }
        $builder->whereHas(
            'user',
            function (Builder $builder) use($user) {
                $builder->where('carrier_id', $user->carrier_id);
            }
        );
        return;
    }
}
