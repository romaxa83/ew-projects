<?php

declare(strict_types=1);

namespace Wezom\Admins\Tests\Feature\Mutations\Back;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Illuminate\Testing\TestResponse;
use JsonException;
use Wezom\Admins\GraphQL\Mutations\Back\BackAdminForgotPassword;
use Wezom\Admins\Models\Admin;
use Wezom\Admins\Notifications\AdminForgotPasswordNotification;
use Wezom\Admins\Testing\TestCase;
use Wezom\Core\Testing\QueryBuilder\GraphQLQuery;

class AdminForgotPasswordTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = BackAdminForgotPassword::NAME;

    /**
     * @throws JsonException
     */
    public function testAdminForgotPassword(): void
    {
        Notification::fake();

        $admin = Admin::factory()->create();

        $this->executeMutation($admin->email)
            ->assertOk();

        Notification::assertSentTo(
            new AnonymousNotifiable(),
            AdminForgotPasswordNotification::class,
            static fn ($notification, $channels, $notifiable) => $notifiable->routes['mail'] === $admin->email
        );
    }

    /**
     * @throws JsonException
     */
    public function testNoValidEmailAdminForgotPassword(): void
    {
        Notification::fake();

        $admin = Admin::factory()->create();

        $result = $this->executeMutation('test@gmail.com')
            ->assertOk();

        Notification::assertNotSentTo(
            new AnonymousNotifiable(),
            AdminForgotPasswordNotification::class,
            static fn ($notification, $channels, $notifiable) => $notifiable->routes['mail'] === $admin->email
        );

        $this->assertResponseHasValidationMessage(
            $result,
            'email',
            [__('admins::validation.admin.admin_not_exists')]
        );
    }

    /**
     * @throws JsonException
     */
    public function testDeactivateAdminForgotPassword(): void
    {
        Notification::fake();

        $admin = Admin::factory()->create(['active' => false]);

        $result = $this->executeMutation($admin->email)
            ->assertOk();

        Notification::assertNotSentTo(
            new AnonymousNotifiable(),
            AdminForgotPasswordNotification::class,
            static fn ($notification, $channels, $notifiable) => $notifiable->routes['mail'] === $admin->email
        );

        $this->assertResponseHasValidationMessage(
            $result,
            'email',
            [__('admins::validation.admin.this_admin_has_been_deactivated')]
        );
    }

    /**
     * @throws JsonException
     */
    private function executeMutation(string $email): TestResponse
    {
        return $this->postGraphQL(
            GraphQLQuery::mutation(self::MUTATION)
                ->args([
                    'email' => $email,
                ])
                ->make()
        )
            ->assertOk();
    }
}
