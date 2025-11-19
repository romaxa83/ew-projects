<?php

declare(strict_types=1);

namespace Wezom\Admins\Tests\Feature\Mutations\Back;

use Exception;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Illuminate\Testing\TestResponse;
use JsonException;
use Wezom\Admins\Enums\AdminStatusEnum;
use Wezom\Admins\GraphQL\Mutations\Back\BackAdminResendEmailVerification;
use Wezom\Admins\Models\Admin;
use Wezom\Admins\Notifications\AdminEmailVerificationNotification;
use Wezom\Admins\Testing\TestCase;
use Wezom\Core\Permissions\Ability;
use Wezom\Core\Testing\QueryBuilder\GraphQLQuery;

class AdminResendEmailVerificationMutationTest extends TestCase
{
    /**
     * @throws JsonException
     * @throws Exception
     */
    public function testResendInviteDoSuccess(): void
    {
        Notification::fake();

        $this->loginAsAdminWithAbility(Ability::toModel(Admin::class)->updateAction());

        $admin = Admin::factory()->create([
            'new_email_for_verification' => 'test@gmail.com', 'status' => AdminStatusEnum::ACTIVE,
        ]);

        $this->executeMutation($admin->getKey())->assertNoErrors()->assertSuccessResponseMessage();

        $this->assertDatabaseHas(Admin::class, [
            'id' => $admin->getKey(),
            'status' => AdminStatusEnum::ACTIVE,
        ]);

        Notification::assertSentTo(
            new AnonymousNotifiable(),
            AdminEmailVerificationNotification::class,
            static fn (
                $notification,
                $channels,
                $notifiable
            ) => $notifiable->routes['mail'] === (string)$admin->new_email_for_verification
        );
    }

    /**
     * @throws JsonException
     */
    public function testResendInviteError(): void
    {
        Notification::fake();

        $this->loginAsAdminWithAbility(Ability::toModel(Admin::class)->updateAction());

        $admin = Admin::factory()->create(['new_email_for_verification' => null]);

        $response = $this->executeMutation($admin->getKey())->assertOk();

        $this->assertGraphQlServerError(
            $response,
            __('admins::exceptions.this_admin_has_not_had_their_email_changed_or_has_already_confirmed')
        );

        Notification::assertNothingSent();
    }

    /**
     * @throws JsonException
     */
    private function executeMutation(int $id): TestResponse
    {
        return $this->postGraphQL(
            GraphQLQuery::mutation(BackAdminResendEmailVerification::getName())
                ->args([
                    'id' => $id,
                ])
                ->select([
                    'message',
                    'type',
                ])
                ->make()
        )
            ->assertOk();
    }
}
