<?php

namespace Wezom\Users\Traits;

use Wezom\Users\Database\Factories\UserFactory;
use Wezom\Users\Models\User;

trait UserTestTrait
{
    protected function loginAsUser(?User $user = null): User
    {
        if (! $user) {
            $user = UserFactory::new()->create();
        }

        $this->actingAs($user, User::GUARD);

        return $user;
    }
}
