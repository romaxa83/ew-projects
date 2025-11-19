<?php

declare(strict_types=1);

namespace Wezom\Admins\Tests\Feature\Mutations\Back;

use Illuminate\Testing\TestResponse;
use Wezom\Admins\Enums\AdminStatusEnum;
use Wezom\Admins\Models\Admin;
use Wezom\Admins\Tests\Feature\AdminTestAbstract;

class BackAdminToggleActiveTest extends AdminTestAbstract
{
    public function testDoSuccess(): void
    {
        $this->loginAsSuperAdmin();

        $admin = Admin::factory()->disabled()->create(['status' => AdminStatusEnum::INACTIVE]);

        $this->assertDatabaseHas(Admin::class, [
            'id' => $admin->getKey(),
            'status' => AdminStatusEnum::INACTIVE,
            'active' => false,
        ]);

        $this->toggleRequest(['ids' => [$admin->getKey()]])->assertNoErrors()->assertSuccessResponseMessage();

        $this->assertDatabaseHas(Admin::class, [
            'id' => $admin->getKey(),
            'active' => true,
            'status' => AdminStatusEnum::ACTIVE,
        ]);
    }

    public function testReverseDoSuccess(): void
    {
        $this->loginAsSuperAdmin();

        $admin = Admin::factory()->create(['status' => AdminStatusEnum::ACTIVE]);

        $this->assertDatabaseHas(Admin::class, [
            'id' => $admin->getKey(),
            'active' => true,
            'status' => AdminStatusEnum::ACTIVE,
        ]);

        $this->toggleRequest(['ids' => [$admin->getKey()]])->assertNoErrors()->assertSuccessResponseMessage();

        $this->assertDatabaseHas(Admin::class, [
            'id' => $admin->getKey(),
            'active' => false,
            'status' => AdminStatusEnum::INACTIVE,
        ]);
    }

    public function testAdminCantToggleItself(): void
    {
        $admin = $this->loginAsSuperAdmin();

        $this->assertDatabaseHas(Admin::class, [
            'id' => $admin->getKey(),
            'active' => true,
            'status' => AdminStatusEnum::ACTIVE,
        ]);

        $result = $this->toggleRequest(['ids' => [$admin->getKey()]])
            ->assertOk();

        $this->assertGraphQlInternal($result, __('admin::messages.admin_fail_reasons_by_myself'));

        $this->assertDatabaseHas(Admin::class, [
            'id' => $admin->getKey(),
            'active' => true,
            'status' => AdminStatusEnum::ACTIVE,
        ]);
    }

    public function testNotPermittedUserGetNoPermissionError(): void
    {
        $this->loginAsAdmin();

        $admin = Admin::factory()->disabled()->create();

        $this->assertDatabaseHas(Admin::class, [
            'id' => $admin->getKey(),
            'active' => false,
        ]);

        $response = $this->toggleRequest(['ids' => [$admin->getKey()]])->assertOk();

        $this->assertGraphQlForbidden($response);

        $this->assertDatabaseHas(Admin::class, [
            'id' => $admin->getKey(),
            'active' => false,
        ]);
    }

    public function testGuestGetUnauthorizedError(): void
    {
        $admin = Admin::factory()->disabled()->create();

        $this->assertDatabaseHas(Admin::class, [
            'id' => $admin->getKey(),
            'active' => false,
        ]);

        $response = $this->toggleRequest(['ids' => [$admin->getKey()]])->assertOk();

        $this->assertGraphQlUnauthorized($response);

        $this->assertDatabaseHas(Admin::class, [
            'id' => $admin->getKey(),
            'active' => false,
        ]);
    }

    protected function toggleRequest(array $args = []): TestResponse
    {
        return $this->mutation($this->operationName())
            ->args($args)->select([
                'message',
                'type',
            ])
            ->executeAndReturnResponse();
    }
}
