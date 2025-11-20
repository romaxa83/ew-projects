<?php

namespace Tests\Feature\Mutations\BackOffice\Auth;

use App\GraphQL\Mutations\BackOffice\Auth\ResetPasswordMutation;
use App\Models\Admins\Admin;
use App\Models\Employees\Employee;
use App\Notifications\Auth\ResetPasswordVerificationNotification;
use App\Services\VerificationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Tests\Builders\Admins\AdminBuilder;
use Tests\Builders\Employees\EmployeeBuilder;
use Tests\TestCase;
use Tests\Traits\Notifications\FakeNotification;

class ResetPasswordMutationTest extends TestCase
{
    use DatabaseTransactions;
    use FakeNotification;

    public const MUTATION = ResetPasswordMutation::NAME;

    private AdminBuilder $adminBuilder;
    private EmployeeBuilder $employeeBuilder;
    protected VerificationService $verificationService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->employeeBuilder = resolve(EmployeeBuilder::class);
        $this->adminBuilder = resolve(AdminBuilder::class);
        $this->verificationService = resolve(VerificationService::class);
    }

    /** @test */
    public function reset_password_as_admin(): void
    {
        Notification::fake();

        /** @var $model Admin */
        $model = $this->adminBuilder->create();

        $password = 'Password1234';

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr([
                'token' => $this->verificationService->encryptEmailToken($model),
                'password' => $password,
                'password_confirmation' => $password,
            ])
        ])
            ->assertOk()
            ->assertJson([
                'data' => [
                    self::MUTATION => true
                ]
            ])
        ;

        $this->assertNotificationSentTo(
            $model->email->getValue(),
            ResetPasswordVerificationNotification::class
        );
    }

    /** @test */
    public function reset_password_as_employee(): void
    {
        Notification::fake();

        /** @var $model Employee */
        $model = $this->employeeBuilder->create();

        $password = 'Password1234';

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr([
                'token' => $this->verificationService->encryptEmailToken($model),
                'password' => $password,
                'password_confirmation' => $password,
            ])
        ])
            ->assertOk()
            ->assertJson([
                'data' => [
                    self::MUTATION => true
                ]
            ])
        ;

        $this->assertNotificationSentTo(
            $model->email->getValue(),
            ResetPasswordVerificationNotification::class
        );
    }

    protected function getQueryStr(array $data): string
    {
        return sprintf(
            '
            mutation {
                %s (
                    token: "%s",
                    password: "%s",
                    password_confirmation: "%s",
                )
            }',
            self::MUTATION,
            data_get($data, 'token'),
            data_get($data, 'password'),
            data_get($data, 'password_confirmation'),
        );
    }
}

