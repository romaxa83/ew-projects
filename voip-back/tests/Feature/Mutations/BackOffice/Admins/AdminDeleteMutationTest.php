<?php

namespace Tests\Feature\Mutations\BackOffice\Admins;

use App\GraphQL\Mutations\BackOffice\Admins\AdminDeleteMutation;
use App\Models\Admins\Admin;
use App\Permissions\Admins\AdminDeletePermission;
use Core\Enums\Messages\MessageTypeEnum;
use Tests\TestCase;
use Tests\Traits\Permissions\RoleHelperHelperTrait;

class AdminDeleteMutationTest extends TestCase
{
    use RoleHelperHelperTrait;

    public const MUTATION = AdminDeleteMutation::NAME;

    public function test_cant_delete_admin_by_not_auth_user(): void
    {
        $deletedAdmin = Admin::factory()->create();

        $query = sprintf(
            'mutation { %s (ids: [%s]) {type message} }',
            self::MUTATION,
            $deletedAdmin->id
        );

        $result = $this->postGraphQLBackOffice(['query' => $query])
            ->assertOk();
        $this->assertGraphQlUnauthorized($result);
    }

    public function test_cant_delete_admin_by_not_permitted_admin(): void
    {
        $this->loginAsAdmin();

        $this->test_cant_delete_admin_by_not_auth_user();
    }

    public function test_a_permitted_admin_can_delete_admin(): void
    {
        $this->loginByAdminManager();
        $deletedAdmin = Admin::factory()->create();

        $this->assertDatabaseHas(Admin::TABLE, [
            'id' => $deletedAdmin->id,
        ]);

        $query = sprintf(
            'mutation { %s (ids: [%s]) {type message} }',
            self::MUTATION,
            $deletedAdmin->id
        );

        $result = $this->postGraphQLBackOffice(['query' => $query]);

        $this->assertEquals(MessageTypeEnum::SUCCESS, $result->json('data.' . self::MUTATION . '.type'));

        $this->assertNull(Admin::find($deletedAdmin->id));
    }

    protected function loginByAdminManager(): Admin
    {
        return $this->loginAsAdmin()
            ->assignRole(
                $this->generateRole('Admin manager', [AdminDeletePermission::KEY], Admin::GUARD)
            );
    }

    public function test_admin_cant_delete_self(): void
    {
        $deletedAdmin = $this->loginByAdminManager();

        $this->assertDatabaseHas(
            Admin::TABLE,
            [
                'id' => $deletedAdmin->id,
            ]
        );

        $query = sprintf(
            'mutation { %s (ids: [%s]) {type message} }',
            self::MUTATION,
            $deletedAdmin->id
        );

        $result = $this->postGraphQLBackOffice(['query' => $query]);
        $this->assertEquals(MessageTypeEnum::WARNING, $result->json('data.' . self::MUTATION . '.type'));
        $this->assertEquals(
            __('messages.admin.actions.delete.fail.reasons.by-myself'),
            $result->json('data.' . self::MUTATION . '.message')
        );

        $this->assertDatabaseHas(
            Admin::TABLE,
            [
                'id' => $deletedAdmin->id,
            ]
        );
    }

    public function test_it_has_not_unique_email_validation_message(): void
    {
        $this->loginByAdminManager();

        $query = sprintf(
            'mutation { %s (ids: [%s]) {type message} }',
            self::MUTATION,
            99999999
        );

        $result = $this->postGraphQLBackOffice(['query' => $query]);
        $this->assertResponseHasValidationMessage(
            $result,
            'ids.0',
            [__('validation.exists', ['attribute' => 'ids.0'])]
        );
    }
}
