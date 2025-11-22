<?php

namespace Tests\Unit\Events\Admins;

use App\Dto\Admins\AdminDto;
use App\Notifications\Admins\AdminCreateNotification;
use App\Services\Admins\AdminService;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AdminSavedEventTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    /**
     * @throws Exception
     */
    public function test_saved_admin(): void
    {
        Notification::fake();

        $adminService = resolve(AdminService::class);
        $adminData = [
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'password' => $this->faker->password,
        ];

        $adminService->create(AdminDto::byArgs($adminData));

        Notification::assertSentTo(new AnonymousNotifiable(), AdminCreateNotification::class);
    }
}
