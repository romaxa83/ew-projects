<?php

namespace Tests;

use App\Helpers\DbConnections;
use App\Models\Admins\Admin;
use App\Models\Users\User;
use Core\Repositories\Passport\PassportClientRepository;
use Illuminate\Support\Facades\Config;
use Illuminate\Testing\TestResponse;

abstract class TestCase extends \Illuminate\Foundation\Testing\TestCase
{
    use CreatesApplication;

    protected array $connectionsToTransact = [
        DbConnections::DEFAULT,
    ];

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('grants.filter_enabled', false);
    }

    protected function postGraphQL(array $data, array $headers = []): TestResponse
    {
        return $this->postJson(config('graphql.route.prefix'), $data, $headers);
    }

    protected function postGraphQLBackOffice(array $data, array $headers = []): TestResponse
    {
        return $this->postJson(config('graphql.route.admin_prefix'), $data, $headers);
    }

    protected function postGraphQlUpload(array $data, array $headers = []): TestResponse
    {
        if (empty($headers)) {
            $headers = ['content-type' => 'multipart/form-data'];
        }

        return $this->post(config('graphql.route.prefix'), $data, $headers);
    }

    protected function postGraphQlBackOfficeUpload(array $data, array $headers = []): TestResponse
    {
        if (empty($headers)) {
            $headers = ['content-type' => 'multipart/form-data'];
        }

        return $this->post(config('graphql.route.admin_prefix'), $data, $headers);
    }

    protected function passportInit(): void
    {
        $this->artisan("passport:client --password --provider=admins --name='Admins'");
        $this->artisan("passport:client --password --provider=users --name='Users'");
        $this->artisan("passport:client --personal --name='Personal'");

        $adminPassportClient = $this->getPassportRepository()->findForAdmin();
        Config::set('auth.oauth_client.admins.id', $adminPassportClient->id);
        Config::set('auth.oauth_client.admins.secret', $adminPassportClient->secret);

        $userPassportClient = $this->getPassportRepository()->findForUser();
        Config::set('auth.oauth_client.users.id', $userPassportClient->id);
        Config::set('auth.oauth_client.users.secret', $userPassportClient->secret);
    }

    protected function getPassportRepository(): PassportClientRepository
    {
        return resolve(PassportClientRepository::class);
    }

    protected function assertUsersHas(array $data, $connection = null): static
    {
        return $this->assertDatabaseHas(User::TABLE, $data, $connection);
    }

    protected function assertUsersMissing(array $data, $connection = null): static
    {
        return $this->assertDatabaseMissing(User::TABLE, $data, $connection);
    }

    protected function loginAsUser(User $user = null, bool $owner = false): User
    {
        if (!$user) {
            $user = User::factory()->withCompany(null, $owner)->create();
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

    protected function assertGraphQlUnauthorized(TestResponse $result): void
    {
        $errors = $result->json('errors');

        self::assertEquals('authorization', array_shift($errors)['extensions']['category']);
    }

    protected function assertGraphQl404(TestResponse $result): void
    {
        $this->assertGraphQlDebugMessage($result, 'No query results for model');
    }

    protected function assertGraphQlDebugMessage(TestResponse $result, string $message): void
    {
        $errors = $result->json('errors');

        $debugMessage = array_shift($errors)['debugMessage'];

        self::assertStringContainsString($message, $debugMessage);
    }

    protected function assertResponseHasValidationMessage(
        TestResponse $result,
        string $attribute,
        array $messages
    ): void {
        $validationMessages = $result->json('errors.0.extensions.validation')[$attribute];

        self::assertTrue(count($validationMessages) > 0);
        self::assertTrue(count($messages) > 0);

        foreach ($messages as $message) {
            $validationMessage = array_shift($validationMessages);
            self::assertEquals($message, $validationMessage);
        }
    }

    protected function assertResponseHasNoValidationErrors(TestResponse $result): void
    {
        self::assertNull($result->json("errors.0.extensions.validation"));
    }

    protected function assertServerError(TestResponse $result, string $message = 'Internal server error'): void
    {
        self::assertEquals($message, $result->json('errors.0.message'));
    }
}
