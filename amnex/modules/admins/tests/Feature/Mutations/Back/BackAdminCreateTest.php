<?php

declare(strict_types=1);

namespace Wezom\Admins\Tests\Feature\Mutations\Back;

use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Illuminate\Testing\TestResponse;
use JsonException;
use Wezom\Admins\Models\Admin;
use Wezom\Admins\Notifications\AdminSetPasswordNotification;
use Wezom\Admins\Tests\Feature\AdminTestAbstract;
use Wezom\Core\Testing\QueryBuilder\GraphQLQuery;

class BackAdminCreateTest extends AdminTestAbstract
{
    /**
     * @throws JsonException
     */
    public function testCantCrateNewAdminForSimpleUser(): void
    {
        $this->loginAsAdmin();

        $attrs = $this->attrs();
        $result = $this->createRequest($attrs);

        $this->assertGraphQlForbidden($result);
    }

    /**
     * @throws JsonException
     */
    public function testCantCreateNewAdminForNotAuthUser(): void
    {
        $attrs = $this->attrs();
        $result = $this->createRequest($attrs);

        $this->assertGraphQlUnauthorized($result);
    }

    /**
     * @throws JsonException
     */
    public function testAPermittedAdminCanCreateNewAdmin(): void
    {
        Notification::fake();

        $this->loginAsSuperAdmin();
        $attrs = $this->attrs();

        $this->assertDatabaseMissing(
            Admin::class,
            [
                'first_name' => $attrs['firstName'],
                'last_name' => $attrs['lastName'],
                'email' => $attrs['email'],
            ]
        );
        $result = $this->createRequest($attrs)->assertNoErrors();
        $createdAdmin = $result->json('data.' . $this->operationName());

        $this->assertNotNull($createdAdmin['id']);
        $this->assertEquals($attrs['firstName'], $createdAdmin['firstName']);
        $this->assertEquals($attrs['lastName'], $createdAdmin['lastName']);
        $this->assertEquals($attrs['email'], $createdAdmin['email']);
        $this->assertEquals($attrs['phone'], $createdAdmin['phone']);
        $this->assertFalse($createdAdmin['inviteAccepted']);
        $this->assertEquals(null, $createdAdmin['newEmailForVerification']);

        $this->assertEquals(
            [
                'id' => $attrs['role']->id,
                'name' => $attrs['role']->name,
            ],
            $createdAdmin['roles'][0]
        );

        $this->assertDatabaseHas(
            Admin::class,
            [
                'first_name' => $attrs['firstName'],
                'last_name' => $attrs['lastName'],
                'email' => $attrs['email'],
            ]
        );

        $this->assertDatabaseHas(
            config('permission.table_names.model_has_roles'),
            [
                'role_id' => $attrs['role']->id,
                'model_id' => $createdAdmin['id'],
                'model_type' => (new Admin())->getMorphClass(),
            ]
        );

        Notification::assertSentTo(
            new AnonymousNotifiable(),
            AdminSetPasswordNotification::class,
            static fn ($notification, $channels, $notifiable) => $notifiable->routes['mail'] === $createdAdmin['email']
        );
    }

    /**
     * @throws JsonException
     */
    public function testItReturnsWrongNameValidationMessage(): void
    {
        $this->loginAsSuperAdmin();
        $attrs = $this->attrs();

        // with digit
        $attrs['firstName'] = 'test 1 name';
        $result = $this->createRequest($attrs);
        $this->assertResponseHasValidationMessage(
            $result,
            'admin.firstName',
            [
                __(
                    'core::validation.custom.name.name-rule',
                    ['attribute' => __('core::validation.attributes.admin.firstName')]
                ),
            ]
        );

        // too short
        $attrs['firstName'] = 't';
        $result = $this->createRequest($attrs);
        $this->assertResponseHasValidationMessage(
            $result,
            'admin.firstName',
            [
                __(
                    'core::validation.custom.name.name-rule',
                    ['attribute' => __('core::validation.attributes.admin.firstName')]
                ),
            ]
        );
    }

    /**
     * @throws JsonException
     */
    public function testItHasNotUniqueEmailValidationMessage(): void
    {
        $existsAdminEmail = 'exists.admin.email@example.com';
        Admin::factory()->create(['email' => $existsAdminEmail]);
        $attrs = $this->attrs();

        $this->loginAsSuperAdmin();
        $attrs['email'] = $existsAdminEmail;
        $result = $this->createRequest($attrs);

        $this->assertResponseHasValidationMessage(
            $result,
            'admin.email',
            [__('validation.unique', ['attribute' => 'admin.email'])]
        );
    }

    /**
     * @throws JsonException
     */
    public function testWrongEmailValidationMessage(): void
    {
        $this->loginAsSuperAdmin();
        $attrs = $this->attrs();

        $attrs['email'] = 'wrong.email@rt';
        $result = $this->createRequest($attrs);

        $this->assertResponseHasValidationMessage(
            $result,
            'admin.email',
            [__('validation.email', ['attribute' => 'admin.email'])]
        );

        $attrs['email'] = 'вкwrong.email@test.com';
        $result = $this->createRequest($attrs);

        $this->assertResponseHasValidationMessage(
            $result,
            'admin.email',
            [__('validation.email', ['attribute' => 'admin.email'])]
        );
    }

    /**
     * @throws JsonException
     */
    protected function createRequest(array $attrs): TestResponse
    {
        return $this->postGraphQL(
            GraphQLQuery::mutation($this->operationName())
                ->args([
                    'admin' => [
                        'firstName' => $attrs['firstName'],
                        'lastName' => $attrs['lastName'],
                        'email' => $attrs['email'],
                        'phone' => $attrs['phone'],
                        'roleId' => $attrs['role']?->id,
                    ],
                ])
                ->select([
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
