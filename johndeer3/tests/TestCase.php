<?php

namespace Tests;

use App\Models\User\User;
use App\Repositories\Auth\PassportClientRepository;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function passportInit(): void
    {
        Artisan::call("passport:client --password --name='Users' --env=testing -n");
//        $this->artisan("passport:client --password --name='Users' --env=testing");

        $userPassportClient = $this->getPassportRepository()->find();

        \Config::set('auth.oauth_secret_id', $userPassportClient->id);
        \Config::set('auth.oauth_secret_key', $userPassportClient->secret);
    }

    protected function getPassportRepository(): PassportClientRepository
    {
        return resolve(PassportClientRepository::class);
    }

    protected function loginAsUser(User $user): User
    {
        $this->actingAs($user, 'api');

        return $user;
    }
}
