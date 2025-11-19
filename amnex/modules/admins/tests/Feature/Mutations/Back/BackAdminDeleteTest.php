<?php

declare(strict_types=1);

namespace Wezom\Admins\Tests\Feature\Mutations\Back;

use Illuminate\Testing\TestResponse;
use Wezom\Admins\Models\Admin;
use Wezom\Admins\Tests\Feature\AdminTestAbstract;

class BackAdminDeleteTest extends AdminTestAbstract
{
    public function testCantDeleteAdminByNotAuthUser(): void
    {
        $deletedAdmin = Admin::factory()->admin()->create();

        $result = $this->deleteRequest($deletedAdmin->id)
            ->assertOk();

        $this->assertGraphQlUnauthorized($result);
    }

    public function testCantDeleteByNotPermittedAdmin(): void
    {
        $this->loginAsAdmin();

        $deletedAdmin = Admin::factory()->admin()->create();

        $result = $this->deleteRequest($deletedAdmin->id)
            ->assertOk();

        $this->assertGraphQlForbidden($result);
    }

    public function testAPermittedAdminCanDeleteAdmin(): void
    {
        $this->loginAsSuperAdmin();
        $deletedAdmin = Admin::factory()->admin()->disabled()->create();

        $this->assertDatabaseHas(
            Admin::class,
            [
                'id' => $deletedAdmin->id,
            ]
        );

        $this->deleteRequest($deletedAdmin->id)->assertNoErrors();

        $this->assertDatabaseMissing(
            Admin::class,
            [
                'id' => $deletedAdmin->id,
            ]
        );
    }

    public function testAdminCantDeleteSelf(): void
    {
        $deletedAdmin = $this->loginAsAdminWithPermissions(['admins.delete']);

        $this->assertDatabaseHas(
            Admin::class,
            [
                'id' => $deletedAdmin->id,
            ]
        );

        $response = $this->deleteRequest($deletedAdmin->id);

        $this->assertGraphQlServerError($response, __('admin::messages.admin_fail_reasons_by_myself'));

        $this->assertDatabaseHas(
            Admin::class,
            [
                'id' => $deletedAdmin->id,
            ]
        );
    }

    public function testDeleteNotExistingAdmin(): void
    {
        $this->loginAsSuperAdmin();

        $this->deleteRequest(999999)->assertNoErrors()->assertFailResponseMessage();
    }

    protected function deleteRequest(int $id): TestResponse
    {
        return $this->mutation($this->operationName())
            ->args([
                'ids' => [$id],
            ])->select([
                'message',
                'type',
            ])
            ->executeAndReturnResponse();
    }
}
