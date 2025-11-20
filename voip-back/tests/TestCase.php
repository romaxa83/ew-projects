<?php

namespace Tests;

use App\Helpers\DbConnections;
use Core\Repositories\Passport\PassportClientRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Config;
use Illuminate\Testing\TestResponse;
use Tests\Traits\InteractsWithAuth;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

abstract class TestCase extends \Illuminate\Foundation\Testing\TestCase
{
    use CreatesApplication;
    use DatabaseTransactions;
    use AdminManagerHelperTrait;
    use InteractsWithAuth;

    protected array $connectionsToTransact = [
        DbConnections::DEFAULT,
        DbConnections::KAMAILIO,
        DbConnections::ASTERISK,
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
        $this->artisan("passport:client --password --provider=employees --name='Employees'");

        $adminPassportClient = $this->getPassportRepository()->findForAdmin();
        Config::set('auth.oauth_client.admins.id', $adminPassportClient->id);
        Config::set('auth.oauth_client.admins.secret', $adminPassportClient->secret);

        $employeePassportClient = $this->getPassportRepository()->findForEmployees();
        Config::set('auth.oauth_client.employees.id', $employeePassportClient->id);
        Config::set('auth.oauth_client.employees.secret', $employeePassportClient->secret);
    }

    protected function getPassportRepository(): PassportClientRepository
    {
        return resolve(PassportClientRepository::class);
    }

    protected function assertGraphQlUnauthorized(TestResponse $result): void
    {
        $errors = $result->json('errors');

        self::assertEquals('authorization', data_get($errors, '0.extensions.category'));
    }

    protected function assertPermission(TestResponse $result): void
    {
        $errors = $result->json('errors');

        self::assertEquals('authorization', data_get($errors, '0.extensions.category'));
        self::assertEquals("No permission", data_get($errors, '0.message'));
    }
    protected function assertUnauthorized(TestResponse $result): void
    {
        $errors = $result->json('errors');

        self::assertEquals('authorization', data_get($errors, '0.extensions.category'));
        self::assertEquals("Unauthorized", data_get($errors, '0.message'));
    }

    protected function assertErrorMessage(TestResponse $result, string $msg): void
    {
        self::assertEquals($msg, $result->json('errors.0.message'));
    }

    protected function assertExceptionMessage(TestResponse $result, string $msg): void
    {
        self::assertEquals($msg, $result->json('errors.0.message'));
        self::assertEquals("translated", $result->json('errors.0.extensions.category'));
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

    protected function assertApiMsgError(TestResponse $result, string $message = 'Internal server error'): void
    {
        self::assertEquals($message, $result->json('data'));
        self::assertFalse($result->json('success'));
    }
}
