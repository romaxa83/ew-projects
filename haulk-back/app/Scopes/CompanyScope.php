<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class CompanyScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (auth()->check() && !is_null($user = authUser())) {
            if ($user->isBroker()) {
                $builder->where('broker_id', $user->broker_id);
                return;
            }

            if ($user->isCarrier()) {
                $builder->where('carrier_id', $user->carrier_id);
                return;
            }

            if ($user->isBodyShopUser()) {
                $builder->where(
                    [
                        ['broker_id', null],
                        ['carrier_id', null],
                    ]
                );
                return;
            }

            $builder->where(
                [
                    ['broker_id', 0],
                    ['carrier_id', 0],
                ]
            );

            return;
        }
    }
}
