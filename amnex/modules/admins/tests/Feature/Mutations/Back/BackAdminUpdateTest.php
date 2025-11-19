<?php

declare(strict_types=1);

namespace Wezom\Admins\Tests\Feature\Mutations\Back;

use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Illuminate\Testing\TestResponse;
use JsonException;
use Wezom\Admins\Enums\AdminStatusEnum;
use Wezom\Admins\Models\Admin;
use Wezom\Admins\Notifications\AdminEmailVerificationNotification;
use Wezom\Admins\Tests\Feature\AdminTestAbstract;
use Wezom\Core\Models\Permission\Role;
use Wezom\Core\Testing\QueryBuilder\GraphQLQuery;

class BackAdminUpdateTest extends AdminTestAbstract
{
    /**
     * @throws JsonException
     */
    public function testCantUpdateAdminByNotAuthUser(): void
    {
        $updatingAdmin = Admin::factory()->admin()->create();

        $attrs = $this->attrs();
        $result = $this->updateRequest($updatingAdmin->getKey(), $attrs);

        $this->assertGraphQlUnauthorized($result);
    }

    /**
     * @throws JsonException
     */
    public function testCantUpdateAdminByNotPermittedAdmin(): void
    {
        $this->loginAsAdmin();

        $updatingAdmin = Admin::factory()->admin()->create();

        $attrs = $this->attrs();
        $result = $this->updateRequest($updatingAdmin->getKey(), $attrs);

        $this->assertGraphQlForbidden($result);
    }

    /**
     * @throws JsonException
     */
    public function testAPermittedAdminCanUpdateAdmin(): void
    {
        $this->loginAsSuperAdmin();
        $updatingAdmin = Admin::factory()->admin()->create();

        $attrs = $this->attrs();
        $attrs['email'] = $updatingAdmin->email;
        $this->assertDatabaseMissing(
            Admin::class,
            [
                'first_name' => $attrs['firstName'],
                'last_name' => $attrs['lastName'],
                'email' => $attrs['email'],
            ]
        );

        $result = $this->updateRequest($updatingAdmin->getKey(), $attrs);

        $updatedAdmin = $result->json('data.' . $this->operationName());

        self::assertNotNull($updatedAdmin['id']);
        self::assertEquals($attrs['firstName'], $updatedAdmin['firstName']);
        self::assertEquals($attrs['lastName'], $updatedAdmin['lastName']);
        self::assertEquals($attrs['email'], $updatedAdmin['email']);

        $this->assertDatabaseHas(
            Admin::class,
            [
                'id' => $updatingAdmin->id,
                'first_name' => $attrs['firstName'],
                'last_name' => $attrs['lastName'],
                'email' => $attrs['email'],
            ]
        );
    }

    /**
     * @throws JsonException
     */
    public function testCheckSendEmailChangeEmail(): void
    {
        Notification::fake();

        $this->loginAsSuperAdmin();

        $admin = Admin::factory()->create(['email_verification_code' => null]);

        $this->assertDatabaseHas(Admin::class, [
            'first_name' => $admin->first_name,
            'last_name' => $admin->last_name,
            'phone' => $admin->phone,
            'new_email_for_verification' => null,
            'status' => AdminStatusEnum::ACTIVE,
        ]);

        $attrs = $this->attrs();

        $response = $this->updateRequest($admin->getKey(), $attrs)->assertNoErrors();

        $admin = $response->json('data.' . $this->operationName());

        $this->assertEquals(true, $admin['inviteAccepted']);
        $this->assertEquals($attrs['email'], $admin['newEmailForVerification']);

        $this->assertDatabaseHas(Admin::class, [
            'first_name' => $attrs['firstName'],
            'last_name' => $attrs['lastName'],
            'phone' => $attrs['phone'],
            'new_email_for_verification' => $attrs['email'],
            'status' => AdminStatusEnum::ACTIVE,
        ]);

        Notification::assertSentTo(
            new AnonymousNotifiable(),
            AdminEmailVerificationNotification::class,
            static fn ($notification, $channels, $notifiable) => $notifiable->routes['mail'] === $attrs['email']
        );
    }

    /**
     * @throws JsonException
     */
    public function testItHasNotUniqueEmailValidationMessage(): void
    {
        $existsAdminEmail = 'exists.admin.email@example.com';
        Admin::factory()->create(['email' => $existsAdminEmail]);
        $updatingAdmin = Admin::factory()->admin()->create();

        $this->loginAsSuperAdmin();

        $attrs = $this->attrs();
        $attrs['email'] = $existsAdminEmail;

        $this->assertDatabaseMissing(
            Admin::class,
            [
                'first_name' => $attrs['firstName'],
                'last_name' => $attrs['lastName'],
                'email' => $attrs['email'],
            ]
        );

        $result = $this->updateRequest($updatingAdmin->getKey(), $attrs);

        $this->assertResponseHasValidationMessage(
            $result,
            'admin.email',
            [__('validation.unique', ['attribute' => 'admin.email'])],
        );
    }

    /**
     * @throws JsonException
     */
    public function testAPermittedAdminCanChangeAdminRole(): void
    {
        $this->loginAsSuperAdmin();
        $updatingAdmin = Admin::factory()->admin()->create();
        $role1 = Role::factory()->admin()->create();
        $role2 = Role::factory()->admin()->create();
        $updatingAdmin->assignRole($role1);

        $this->assertDatabaseMissing(
            config('permission.table_names.model_has_roles'),
            [
                'role_id' => $role2->id,
                'model_id' => $updatingAdmin->id,
                'model_type' => 'admin',
            ]
        );

        $attrs = $this->attrs();

        $attrs['role'] = $role2;

        $result = $this->updateRequest($updatingAdmin->getKey(), $attrs);

        $updatedAdmin = $result->json('data.' . $this->operationName());

        self::assertEquals(
            [
                [
                    'id' => $role2->id,
                    'name' => $role2->name,
                ],
            ],
            $updatedAdmin['roles']
        );

        $this->assertDatabaseHas(
            config('permission.table_names.model_has_roles'),
            [
                'role_id' => $role2->id,
                'model_type' => 'admin',
                'model_id' => $updatingAdmin->id,
            ]
        );
    }

    /**
     * @throws JsonException
     */
    public function testAPermittedAdminCanUpdateManager(): void
    {
        $this->loginAsSuperAdmin();
        Role::factory()->admin()->create(['name' => 'Manager']);
        $updatingAdmin = Admin::factory()->manager()->create();

        $attrs = $this->attrs();
        $attrs['email'] = $updatingAdmin->email;

        $this->assertDatabaseMissing(
            Admin::class,
            [
                'first_name' => $attrs['firstName'],
                'last_name' => $attrs['lastName'],
                'email' => $attrs['email'],
            ]
        );

        $result = $this->updateRequest($updatingAdmin->getKey(), $attrs);

        $updatedAdmin = $result->json('data.' . $this->operationName());

        self::assertNotNull($updatedAdmin['id']);
        self::assertEquals($attrs['firstName'], $updatedAdmin['firstName']);
        self::assertEquals($attrs['lastName'], $updatedAdmin['lastName']);
        self::assertEquals($attrs['email'], $updatedAdmin['email']);

        $this->assertDatabaseHas(
            Admin::class,
            [
                'id' => $updatingAdmin->id,
                'first_name' => $attrs['firstName'],
                'last_name' => $attrs['lastName'],
                'email' => $attrs['email'],
            ]
        );
    }

    /**
     * @throws JsonException
     */
    public function testChangeRoleToAdmin(): void
    {
        $this->loginAsSuperAdmin();
        $updatingAdmin = Admin::factory()->manager()->create();
        $adminRole = Role::factory()->admin()->create(['name' => 'Manager']);

        $attrs = $this->attrs();
        $attrs['role'] = $adminRole;
        $result = $this->updateRequest($updatingAdmin->getKey(), $attrs);

        $updatedAdmin = $result->json('data.' . $this->operationName());

        self::assertEquals($adminRole->id, $updatedAdmin['roles'][0]['id']);
    }

    /**
     * @throws JsonException
     */
    protected function updateRequest(
        int $id,
        array $attrs
    ): TestResponse {
        return $this->postGraphQL(
            GraphQLQuery::mutation($this->operationName())
                ->args([
                    'id' => $id,
                    'admin' => [
                        'firstName' => $attrs['firstName'],
                        'lastName' => $attrs['lastName'],
                        'email' => $attrs['email'],
                        'phone' => $attrs['phone'],
                        'roleId' => $attrs['role']->id,
                    ],
                ])->select([
                    'id',
                    'firstName',
                    'lastName',
                    'email',
                    'phone',
                    'roles' => [
                        'id',
                        'name',
                    ],
                    'active',
                    'inviteAccepted',
                    'newEmailForVerification',
                    'status',
                ])
                ->make()
        );
    }
}
