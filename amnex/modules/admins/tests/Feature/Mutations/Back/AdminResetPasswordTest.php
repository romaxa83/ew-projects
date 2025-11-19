<?php

declare(strict_types=1);

namespace Wezom\Admins\Tests\Feature\Mutations\Back;

use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Testing\TestResponse;
use JsonException;
use Wezom\Admins\Enums\AdminStatusEnum;
use Wezom\Admins\GraphQL\Mutations\Back\BackAdminResetPassword;
use Wezom\Admins\GraphQL\Queries\Back\BackAdmins;
use Wezom\Admins\Models\Admin;
use Wezom\Admins\Services\AdminVerificationService;
use Wezom\Admins\Testing\TestCase;
use Wezom\Core\Enums\ActiveTypeEnum;
use Wezom\Core\Testing\QueryBuilder\GraphQLQuery;

class AdminResetPasswordTest extends TestCase
{
    private AdminVerificationService $adminVerificationService;

    /**
     * @throws Exception
     */
    public function testUserResetPassword(): void
    {
        $admin = Admin::factory()->create(['status' => AdminStatusEnum::ACTIVE]);

        $password = 'newPassword123@';

        self::assertFalse(
            Hash::check($password, $admin->password)
        );
        $token = $this->adminVerificationService->encryptTokenForResetPassword(
            $admin,
            now()->addHour()->getTimestamp()
        );

        $this->executeMutation([
            'token' => $token,
            'password' => $password,
            'passwordConfirmation' => $password,
        ])
            ->assertOk();

        $admin->refresh();

        self::assertTrue(
            Hash::check($password, $admin->password)
        );

        $this->assertDatabaseHas(Admin::class, [
            'id' => $admin->getKey(),
            'status' => AdminStatusEnum::ACTIVE,
        ]);

        $admin = $this->loginAsAdminWithPermissions();

        $query = sprintf(
            'query { %s(ids: %s) {
                        data { id newEmailForVerification inviteAccepted}
                    } }',
            BackAdmins::getName(),
            $admin->id
        );

        $response = $this->postGraphQL(compact('query'))
            ->assertOk();

        $admins = $response->json('data.' . BackAdmins::getName() . '.data');

        self::assertCount(1, $admins);

        $this->assertTrue($admins[0]['inviteAccepted']);
        $this->assertNull($admins[0]['newEmailForVerification']);
    }

    public function testThrowsExceptionWhenAdminNotFound(): void
    {
        $admin = Admin::factory()->create(['active' => ActiveTypeEnum::DISABLED, 'status' => AdminStatusEnum::PENDING]);

        $password = 'newPassword123@';

        self::assertFalse(
            Hash::check($password, $admin->password)
        );
        $token = $this->adminVerificationService->encryptTokenForResetPassword(
            $admin,
            now()->addHour()->getTimestamp()
        );

        $response = $this->executeMutation([
            'token' => $token,
            'password' => $password,
            'passwordConfirmation' => $password,
        ])
            ->assertOk();

        $this->assertGraphQlServerError(
            $response,
            __('admins::exceptions.no_active_user_with_this_email')
        );
    }

    public function testThrowsExceptionWhenTimedOut(): void
    {
        $admin = Admin::factory()->create(['status' => AdminStatusEnum::PENDING]);

        $password = 'newPassword123@';

        self::assertFalse(
            Hash::check($password, $admin->password)
        );
        $token = $this->adminVerificationService->encryptTokenForResetPassword(
            $admin,
            now()->subWeek()->getTimestamp()
        );

        $response = $this->executeMutation([
            'token' => $token,
            'password' => $password,
            'passwordConfirmation' => $password,
        ])
            ->assertOk();

        $this->assertGraphQlServerError(
            $response,
            __('admins::exceptions.password_reset_timed_out')
        );
    }

    public function testThrowsExceptionWhenCodeMismatch(): void
    {
        $admin = Admin::factory()->create(['status' => AdminStatusEnum::ACTIVE]);

        $password = 'newPassword123@';

        self::assertFalse(
            Hash::check($password, $admin->password)
        );
        $token = $this->adminVerificationService->encryptTokenForResetPassword(
            $admin,
            now()->addHour()->getTimestamp()
        );
        $this->adminVerificationService->fillEmailVerificationCode($admin);

        $response = $this->executeMutation([
            'token' => $token,
            'password' => $password,
            'passwordConfirmation' => $password,
        ])
            ->assertOk();

        $this->assertGraphQlServerError(
            $response,
            __('admins::exceptions.invalid_password_reset_link')
        );
    }

    /**
     * @throws JsonException
     */
    private function executeMutation(array $args): TestResponse
    {
        return $this->postGraphQL(
            GraphQLQuery::mutation(BackAdminResetPassword::getName())
                ->args([
                    'token' => $args['token'],
                    'password' => $args['password'],
                    'passwordConfirmation' => $args['passwordConfirmation'],
                ])
                ->make()
        )
            ->assertOk();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminVerificationService = app(AdminVerificationService::class);
    }
}
