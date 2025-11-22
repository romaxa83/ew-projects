<?php

namespace Tests;

use App\Foundations\Modules\Auth\Services\Passport\PassportClientRepository;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Config;
use Tests\Traits\Assert\AssertErrors;
use Tests\Traits\Assert\AssertMsg;
use Tests\Traits\Assert\AssertNotification;
use Tests\Traits\InteractsWithAuth;
use Tests\Traits\RequestToEComm;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use InteractsWithAuth;
    use AssertErrors;
    use AssertMsg;
    use AssertNotification;
    use RequestToEComm;

    protected const FAKE_DISK_STORAGE = 'public';

    protected function setUp(): void
    {
        parent::setUp();

        $this->passportInit();
    }

    protected function passportInit(): void
    {
        /**  @var $passportRepository PassportClientRepository */
        $passportRepository = resolve(PassportClientRepository::class);

        $this->artisan("passport:client --password --provider=admins --name='Admins'");
        $this->artisan("passport:client --password --provider=users --name='Users'");

        $adminPassportClient = $passportRepository->findForAdmin();
        Config::set('auth.oauth_client.admins.id', $adminPassportClient->id);
        Config::set('auth.oauth_client.admins.secret', $adminPassportClient->secret);

        $userPassportClient = $passportRepository->findForUser();
        Config::set('auth.oauth_client.users.id', $userPassportClient->id);
        Config::set('auth.oauth_client.users.secret', $userPassportClient->secret);
    }
}
