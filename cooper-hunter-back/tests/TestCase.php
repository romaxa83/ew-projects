<?php

namespace Tests;

use App\Helpers\DbConnections;
use App\Models\Users\User;
use Core\Repositories\Passport\PassportClientRepository;
use Illuminate\Support\Facades\Config;
use Illuminate\Testing\TestResponse;
use Tests\Traits\InteractsWithAuth;

abstract class TestCase extends \Illuminate\Foundation\Testing\TestCase
{
    use CreatesApplication;
    use InteractsWithAuth;

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
        return $this->postJson(config('graphql.prefix'), $data, $headers);
    }

    protected function postGraphQLBackOffice(array $data, array $headers = []): TestResponse
    {
        return $this->postJson(config('graphql.admin_prefix'), $data, $headers);
    }

    protected function postGraphQlUpload(array $data, array $headers = []): TestResponse
    {
        if (empty($headers)) {
            $headers = ['content-type' => 'multipart/form-data'];
        }

        return $this->post(config('graphql.prefix'), $data, $headers);
    }

    protected function postGraphQlBackOfficeUpload(array $data, array $headers = []): TestResponse
    {
        if (empty($headers)) {
            $headers = ['content-type' => 'multipart/form-data'];
        }

        return $this->post(config('graphql.admin_prefix'), $data, $headers);
    }

    protected function passportInit(): void
    {
        $this->artisan("passport:client --password --provider=admins --name='Admins'");
        $this->artisan("passport:client --password --provider=users --name='Users'");
        $this->artisan("passport:client --password --provider=technicians --name='Technicians'");
        $this->artisan("passport:client --password --provider=1c_moderators --name='1CModerators'");
        $this->artisan("passport:client --password --provider=dealers --name='Dealers'");
        $this->artisan("passport:client --personal --name='Personal'");

        $adminPassportClient = $this->getPassportRepository()->findForAdmin();
        Config::set('auth.oauth_client.admins.id', $adminPassportClient->id);
        Config::set('auth.oauth_client.admins.secret', $adminPassportClient->secret);

        $userPassportClient = $this->getPassportRepository()->findForUser();
        Config::set('auth.oauth_client.users.id', $userPassportClient->id);
        Config::set('auth.oauth_client.users.secret', $userPassportClient->secret);

        $technicianPassportClient = $this->getPassportRepository()->findForTechnician();
        Config::set('auth.oauth_client.technicians.id', $technicianPassportClient->id);
        Config::set('auth.oauth_client.technicians.secret', $technicianPassportClient->secret);

        $moderatorPassportClient = $this->getPassportRepository()->findForModerator();
        Config::set('auth.oauth_client.1c_moderators.id', $moderatorPassportClient->id);
        Config::set('auth.oauth_client.1c_moderators.secret', $moderatorPassportClient->secret);

        $dealerPassportClient = $this->getPassportRepository()->findForDealer();
        Config::set('auth.oauth_client.dealers.id', $dealerPassportClient->id);
        Config::set('auth.oauth_client.dealers.secret', $dealerPassportClient->secret);
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
        self::assertNotEmpty($validationMessages);
        self::assertNotEmpty($messages);

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

    protected function assertErrorMessage(TestResponse $result, string $msg): void
    {
        self::assertEquals($msg, $result->json('errors.0.message'));
    }

    protected function assertErrorMessageFirst(TestResponse $result, string $msg): void
    {
        self::assertEquals($msg, $result->json('errors.0.messages.0'));
    }

    protected function assertTranslatedMessage(TestResponse $result, string $msg): void
    {
        self::assertEquals($msg, $result->json('errors.0.message'));
        self::assertEquals('translated', $result->json('errors.0.extensions.category'));
    }
}
