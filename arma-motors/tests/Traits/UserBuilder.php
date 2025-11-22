<?php

namespace Tests\Traits;

trait UserBuilder
{
    public function userBuilder()
    {
        return new \Tests\_Helpers\UserBuilder();
    }
}
