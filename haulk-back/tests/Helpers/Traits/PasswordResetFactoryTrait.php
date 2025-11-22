<?php

namespace Tests\Helpers\Traits;

use App\Models\PasswordReset;

trait PasswordResetFactoryTrait
{
    public function createToken($attrs = [])
    {
        return factory(PasswordReset::class)->create($attrs);
    }
}
