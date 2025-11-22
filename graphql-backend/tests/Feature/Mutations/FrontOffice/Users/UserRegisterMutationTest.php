<?php

namespace Tests\Feature\Mutations\FrontOffice\Users;

use App\GraphQL\Mutations\FrontOffice\Users\UserRegisterMutation;
use App\Models\Companies\Company;
use App\Models\Permissions\Role;
use App\Models\Users\User;
use App\Notifications\Users\UserEmailVerification;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;
use Tests\Traits\ValidationErrors;

class UserRegisterMutationTest extends TestCase
{
    use DatabaseTransactions;
    use ValidationErrors;

    public const MUTATION = UserRegisterMutation::NAME;
    public const USER_EMAIL = 'new_user@example.com';

    protected array $data = [];
    protected Role $ownerRole;

    public function test_company_automatic_create_after_user_register(): void
    {
        $this->query()
            ->assertOk();

        $user = User::query()->where('email', self::USER_EMAIL)->first();
        self::assertNotNull($user);

        $this->assertDatabaseHas(
            'company_user',
            [
                'user_id' => $user->id,
                'state' => Company::STATE_OWNER
            ]
        );
    }

    protected function query(): TestResponse
    {
        return $this->postGraphQL(
            [
                'query' => sprintf(
                    'mutation {
                                %s (
                                   first_name: "%s",
                                   last_name: "%s",
                                   middle_name: "%s",
                                   email: "%s",
                                   password: "%s",
                                   password_confirmation: "%s",
                                ) {
                                    refresh_token
                                    access_expires_in
                                    refresh_expires_in
                                    token_type
                                    access_token
                                }
                            }',
                    self::MUTATION,
                    $this->data['firstName'],
                    $this->data['lastName'],
                    $this->data['middleName'],
                    $this->data['email'],
                    $this->data['password'],
                    $this->data['password']
                ),
            ]
        );
    }

    public function test_new_user_register_success(): void
    {
        $result = $this->query()
            ->assertOk();

        [self::MUTATION => $data] = $result->json('data');

        self::assertArrayHasKey('refresh_token', $data);
        self::assertArrayHasKey('access_expires_in', $data);
        self::assertArrayHasKey('refresh_expires_in', $data);
        self::assertArrayHasKey('token_type', $data);
        self::assertArrayHasKey('access_token', $data);
    }

    public function test_it_returns_email_validation_error_on_duplicate_user_email(): void
    {
        User::factory()->create(
            [
                'email' => static::USER_EMAIL,
                'lang' => 'uk'
            ]
        );

        $result = $this->query();

        $this->assertResponseHasValidationMessage(
            $result,
            'email',
            [
                __('validation.unique_email'),
            ]
        );
    }

    public function test_new_user_has_default_language(): void
    {
        $this->query()
            ->assertOk();

        $langService = resolve('localization');

        $this->assertDatabaseHas(
            User::TABLE,
            [
                'first_name' => $this->data['firstName'],
                'email' => $this->data['email'],
                'lang' => $langService->getDefaultSlug()
            ]
        );
    }

    public function test_new_owner_has_role(): void
    {
        Role::factory()->create();

        $this->query()
            ->assertOk();

        $user = User::query()
            ->where('email', $this->data['email'])
            ->first();

        self::assertEquals($this->ownerRole->id, $user->role->id);
    }

    /**
     * @throws Exception
     */
    public function test_new_user_get_email_verification(): void
    {
        Notification::fake();

        $this->query();
        $newUser = User::query()->first();

        Notification::assertSentTo(
            new AnonymousNotifiable(),
            UserEmailVerification::class,
            function ($notification, $channels, $notifiable) use ($newUser) {
                return $notifiable->routes['mail'] === $newUser->getEmailString();
            }
        );
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->data = [
            'firstName' => 'First',
            'lastName' => 'Last',
            'middleName' => 'Middle',
            'email' => self::USER_EMAIL,
            'phone' => '+38 (099) 123-45-67',
            'password' => 'password1',
        ];

        $this->passportInit();

        Http::fake();

        $this->ownerRole = Role::factory()
            ->asDefault()
            ->create();
    }
}
