<?php

namespace Tests\Unit\Services\Users;

use App\Dto\Users\UserDto;
use App\Events\Users\UserRegisteredEvent;
use App\Services\Users\UserService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;
use Throwable;

class UserServiceTest extends TestCase
{
    use DatabaseTransactions;

    private UserService $service;

    /**
     * @throws Throwable
     */
    public function test_it_has_user_registered_event_when_new_user_registered(): void
    {
        Event::fake();

        $email = 'user@example.com';
        $dto = UserDto::byArgs(
            [
                'first_name' => 'First',
                'last_name' => 'Last',
                'email' => $email,
                'phone' => '+380991234567',
                'password' => 'password',
            ]
        );

        $this->assertUsersMissing(['email' => $email]);

        $this->service->register($dto);

        $this->assertUsersHas(['email' => $email]);

        Event::assertDispatched(UserRegisteredEvent::class);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = $this->app->make(UserService::class);
    }
}
