<?php

namespace Tests\Feature\Mutations\FrontOffice\Users;

use App\GraphQL\Mutations\FrontOffice\Users\UserRegisterMutation;
use App\Models\Auth\MemberPhoneVerification;
use App\Models\Permissions\Role;
use App\Models\Users\User;
use App\Notifications\Members\MemberEmailVerification;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Event;
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

    public function test_new_user_register_success(): void
    {
        Notification::fake();

        $result = $this->query()
            ->assertOk();

        [self::MUTATION => $data] = $result->json('data');

        self::assertArrayHasKey('refresh_token', $data);
        self::assertArrayHasKey('access_expires_in', $data);
        self::assertArrayHasKey('refresh_expires_in', $data);
        self::assertArrayHasKey('token_type', $data);
        self::assertArrayHasKey('access_token', $data);
    }

    protected function query(string $args = ''): TestResponse
    {
        return $this->postGraphQL(
            [
                'query' => sprintf(
                    'mutation {
                                %s (
                                   first_name: "%s",
                                   last_name: "%s",
                                   email: "%s",
                                   password: "%s",
                                   password_confirmation: "%s",
                                   %s
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
                    $this->data['email'],
                    $this->data['password'],
                    $this->data['password'],
                    $args
                ),
            ]
        );
    }

    public function test_it_returns_email_validation_error_on_duplicate_user_email(): void
    {
        User::factory()->create(
            [
                'email' => static::USER_EMAIL,
                'lang' => 'en'
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
        Notification::fake();

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
            MemberEmailVerification::class,
            static function ($notification, $channels, $notifiable) use ($newUser) {
                return $notifiable->routes['mail'] === $newUser->getEmailString();
            }
        );
    }

    public function test_validate_only_registration(): void
    {
        $this->query('validate_only: true')
            ->assertJsonStructure(
                [
                    'errors' => [
                        [
                            'message',
                            'extensions' => []
                        ],
                    ],
                ]
            );

        $this->assertDatabaseMissing(
            User::TABLE,
            [
                'email' => $this->data['email'],
            ]
        );
    }

    public function test_confirm_phone(): void
    {
        Event::fake();

        $code = MemberPhoneVerification::factory()->withAccessToken()->create();

        $this->query(
            sprintf('sms_access_token: "%s"', $code->access_token)
        )
            ->assertOk();

        $user = User::query()->where('email', $this->data['email'])->first();

        self::assertNotNull($user->phone_verified_at);

        $this->assertDatabaseMissing(
            MemberPhoneVerification::TABLE,
            [
                'id' => $code->id
            ]
        );
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->data = [
            'firstName' => 'First',
            'lastName' => 'Last',
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
