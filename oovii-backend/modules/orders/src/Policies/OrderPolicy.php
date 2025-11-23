<?php

namespace WezomCms\Orders\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use WezomCms\Orders\Models\Order;
use WezomCms\Users\Models\User;

class OrderPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function update(User $user, Order $order): bool
    {
        return $order->user_id === $user->id;
    }

    public function delete(User $user, Order $order): bool
    {
        return $order->user_id === $user->id;
    }
}
