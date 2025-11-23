<?php

namespace Tests;

use App\Helpers\DbConnections;
use App\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\Builders\Users\UserBuilder;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected array $connectionsToTransact = [
        DbConnections::DEFAULT,
        DbConnections::RINGOSTAT,
        DbConnections::AUDIT,
    ];



    protected function loginUser(User $model = null): User
    {
        if(!$model){
            $builder = resolve(UserBuilder::class);
            $model = $builder->create();
        }

        $this->actingAs($model);

        return $model;
    }
}
