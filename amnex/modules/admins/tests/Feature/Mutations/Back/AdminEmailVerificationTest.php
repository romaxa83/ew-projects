<?php

namespace Wezom\Admins\Tests\Feature\Mutations\Back;

use Exception;
use Illuminate\Testing\TestResponse;
use JsonException;
use Wezom\Admins\GraphQL\Mutations\Back\BackAdminEmailVerification;
use Wezom\Admins\GraphQL\Queries\Back\BackAdmins;
use Wezom\Admins\Models\Admin;
use Wezom\Admins\Services\AdminVerificationService;
use Wezom\Admins\Testing\TestCase;
use Wezom\Core\Enums\ActiveTypeEnum;
use Wezom\Core\Testing\QueryBuilder\GraphQLQuery;

class AdminEmailVerificationTest extends TestCase
{
    private AdminVerificationService $adminVerificationService;

    /**
     * @throws Exception
     */
    public function testAdminEmailVerification(): void
    {
        $oldEmail = 'test@gmail.com';
        $newEmail = 'newtest@gmail.com';
        $admin = Admin::factory()->create([
            'email' => $oldEmail,
            'new_email_for_verification' => $newEmail,
        ]);
        $token = $this->adminVerificationService
            ->encryptTokenForEmailVerification($admin, now()->addHour()->getTimestamp());

        $this->executeMutation($token)->assertOk();

        $admin->refresh();

        self::assertEquals($newEmail, (string)$admin->email);
        self::assertNull($admin->new_email_for_verification);
        self::assertNull($admin->new_email_verification_code);

        $this->loginAsSuperAdmin();

        $response = $this->postGraphQL(
            GraphQLQuery::query(BackAdmins::getName())
                ->args([
                    'ids' => [$admin->id],
                ])
                ->select([
                    'data' => [
                        'id',
                        'newEmailForVerification',
                        'inviteAccepted',
                    ],
                ])
                ->make()
        )
            ->assertNoErrors();

        $admins = $response->json('data.' . BackAdmins::getName() . '.data');

        self::assertCount(1, $admins);

        $this->assertTrue($admins[0]['inviteAccepted']);
        $this->assertNull($admins[0]['newEmailForVerification']);
    }

    public function testThrowsExceptionWhenAdminNotFound(): void
    {
        $admin = Admin::factory(['active' => ActiveTypeEnum::DISABLED])->create();
        $token = $this->adminVerificationService
            ->encryptTokenForEmailVerification($admin, now()->addHour()->getTimestamp());

        $response = $this->executeMutation($token)->assertOk();

        $this->assertGraphQlServerError(
            $response,
            __('admins::exceptions.no_active_user_with_this_email')
        );
    }

    public function testThrowsExceptionWhenTimedOut(): void
    {
        $admin = Admin::factory()->create();
        $token = $this->adminVerificationService
            ->encryptTokenForEmailVerification($admin, now()->subWeek()->getTimestamp());

        $response = $this->executeMutation($token)->assertOk();

        $this->assertGraphQlServerError(
            $response,
            __('admins::exceptions.change_email_timed_out')
        );
    }

    public function testThrowsExceptionWhenCodeMismatch(): void
    {
        $admin = Admin::factory()->create();
        $token = $this->adminVerificationService
            ->encryptTokenForEmailVerification($admin, now()->addHour()->getTimestamp());
        $this->adminVerificationService->fillNewEmailVerificationCode($admin);

        $response = $this->executeMutation($token)->assertOk();

        $this->assertGraphQlServerError(
            $response,
            __('admins::exceptions.invalid_change_email_link')
        );
    }

    /**
     * @throws JsonException
     */
    private function executeMutation(string $token): TestResponse
    {
        return $this->postGraphQL(
            GraphQLQuery::mutation(BackAdminEmailVerification::getName())
                ->args([
                    'token' => $token,
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
