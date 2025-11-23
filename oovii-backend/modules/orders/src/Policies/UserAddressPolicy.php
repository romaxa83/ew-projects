<?php

namespace WezomCms\Orders\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use WezomCms\Orders\Models\UserAddress;
use WezomCms\Users\Models\User;

class UserAddressPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function update(User $user, UserAddress $address): bool
    {
        return $address->user_id === $user->id;
    }

    public function delete(User $user, UserAddress $address): bool
    {
        return $address->user_id === $user->id;
    }
}
