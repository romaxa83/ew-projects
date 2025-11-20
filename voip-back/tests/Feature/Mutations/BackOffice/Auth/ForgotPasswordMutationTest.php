<?php

namespace Tests\Feature\Mutations\BackOffice\Auth;

use App\GraphQL\Mutations\BackOffice\Auth\ForgotPasswordMutation;
use App\Models\Admins\Admin;
use App\Models\Employees\Employee;
use App\Notifications\Auth\ForgotPasswordVerificationNotification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Tests\Builders\Admins\AdminBuilder;
use Tests\Builders\Employees\EmployeeBuilder;
use Tests\TestCase;
use Tests\Traits\Notifications\FakeNotification;

class ForgotPasswordMutationTest extends TestCase
{
    use DatabaseTransactions;
    use FakeNotification;

    public const MUTATION = ForgotPasswordMutation::NAME;

    private AdminBuilder $adminBuilder;
    private EmployeeBuilder $employeeBuilder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->employeeBuilder = resolve(EmployeeBuilder::class);
        $this->adminBuilder = resolve(AdminBuilder::class);
    }

    /** @test */
    public function forgot_password_as_admin(): void
    {
        Notification::fake();

        /** @var $model Admin */
        $model = $this->adminBuilder->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($model->email->getValue())
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
            ForgotPasswordVerificationNotification::class
        );
    }

    /** @test */
    public function forgot_password_as_employee(): void
    {
        Notification::fake();

        /** @var $model Employee */
        $model = $this->employeeBuilder->create();

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($model->email->getValue())
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
            ForgotPasswordVerificationNotification::class
        );
    }

    /** @test */
    public function fail_wrong_email(): void
    {
        Notification::fake();

        $email = 'test@test.com';

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($email)
        ])
        ;

        $this->assertResponseHasValidationMessage($res, 'email',[
            'The selected email is invalid.'
        ]);

        $this->assertNotificationNotSentTo(
            $email,
            ForgotPasswordVerificationNotification::class
        );
    }

    protected function getQueryStr(string $email): string
    {
        return sprintf(
            '
            mutation {
                %s (
                    email: "%s",
                )
            }',
            self::MUTATION,
            $email,
        );
    }
}
