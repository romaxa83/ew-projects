<?php

namespace Tests;

use App\Models\Admin\Admin;
use App\Models\User\User;
use App\Repositories\Passport\PassportClientRepository;
use Config;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Testing\TestResponse;
use Nuwave\Lighthouse\Testing\MakesGraphQLRequests;
use Tests\_Helpers\MailerClient;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use MakesGraphQLRequests;
//    use AdminBuilder;

    private ?MailerClient $mailer = null;

    protected function postGraphQL(array $data, array $headers = []): TestResponse
    {
        return $this->postJson(config('lighthouse.route.uri'), $data, $headers);
    }

    protected function passportInit(): void
    {
        $this->artisan("passport:client --password --provider=admins --name='Admins'");
        $this->artisan("passport:client --password --provider=users --name='Users'");

        $adminPassportClient = $this->getPassportRepository()->findForAdmin();
        Config::set('auth.oauth_client.admins.id', $adminPassportClient->id);
        Config::set('auth.oauth_client.admins.secret', $adminPassportClient->secret);

        $userPassportClient = $this->getPassportRepository()->findForUser();
        Config::set('auth.oauth_client.users.id', $userPassportClient->id);
        Config::set('auth.oauth_client.users.secret', $userPassportClient->secret);
    }

    protected function armaAuth()
    {
        Config::set('aa.access.login', env('AA_LOGIN'));
        Config::set('aa.access.password', env('AA_PASSWORD'));
    }

    protected function getPassportRepository(): PassportClientRepository
    {
        return resolve(PassportClientRepository::class);
    }

    protected function assertUsersHas(array $data, $connection = null): static
    {
        return $this->assertDatabaseHas(User::TABLE_NAME, $data, $connection);
    }

    protected function assertUsersMissing(array $data, $connection = null): static
    {
        return $this->assertDatabaseMissing(User::TABLE_NAME, $data, $connection);
    }

    protected function loginAsUser(User $user = null): User
    {
        if (!$user) {
            $user = User::factory()->create();
        }

        $this->actingAs($user, User::GUARD);

        return $user;
    }

    protected function loginAsAdmin(Admin $admin = null): Admin
    {
        if (!$admin) {
            $admin = Admin::factory()->create();
        }

        $this->actingAs($admin, Admin::GUARD);

        return $admin;
    }

    protected function assertGrapthQlUnauthorized(TestResponse $result)
    {
        $errors = $result->json('errors');

        $this->assertEquals('Unauthorized', array_shift($errors)['message']);
    }

    protected function assertResponseHasValidationMessage(TestResponse $result, string $attribute, array $messages)
    {
        $this->assertEquals($messages, $result->json("errors.0.extensions.validation.$attribute"));
    }

    protected function assertServerError(TestResponse $result, string $message = 'Internal server error')
    {
        $this->assertEquals($message, $result->json('errors.0.message'));
    }

    protected function mailer(): MailerClient
    {
        if ($this->mailer === null) {
            $this->mailer = new MailerClient();
        }
        return $this->mailer;
    }
}
