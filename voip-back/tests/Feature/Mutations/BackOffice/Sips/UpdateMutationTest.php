<?php

namespace Tests\Feature\Mutations\BackOffice\Sips;

use App\GraphQL\Mutations\BackOffice;
use App\IPTelephony\Events\Subscriber\SubscriberUpdateOrCreateEvent;
use App\IPTelephony\Listeners\Subscriber\SubscriberUpdateOrInsertListener;
use App\Models\Employees\Employee;
use App\Models\Sips\Sip;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Tests\Builders\Employees\EmployeeBuilder;
use Tests\Builders\Sips\SipBuilder;
use Tests\TestCase;

class UpdateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    protected EmployeeBuilder $employeeBuilder;
    protected SipBuilder $sipBuilder;

    protected array $data;

    public const MUTATION = BackOffice\Sips\SipUpdateMutation::NAME;

    public function setUp(): void
    {
        parent::setUp();

        $this->sipBuilder = resolve(SipBuilder::class);
        $this->employeeBuilder = resolve(EmployeeBuilder::class);

        $this->data = [
            'number' => '333',
            'password' => 'Password1324234',
        ];
    }

    /** @test */
    public function success_update_without_employee(): void
    {
        Event::fake([SubscriberUpdateOrCreateEvent::class]);

        $this->loginAsSuperAdmin();

        /** @var $model Sip */
        $model = $this->sipBuilder->create();

        $this->data['id'] = $model->id;

        $this->assertNull($model->employee);
        $this->assertNotEquals($model->number, data_get($this->data, 'number'));
        $this->assertNotEquals($model->password, data_get($this->data, 'password'));

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($this->data)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'id' => $model->id,
                        'employee' => null,
                        'number' => data_get($this->data, 'number'),
                    ],
                ]
            ])
        ;

        $model->refresh();

        $this->assertEquals($model->password, data_get($this->data, 'password'));

        Event::assertNotDispatched(SubscriberUpdateOrCreateEvent::class);
    }

    /** @test */
    public function success_update_with_employee_has_subscriber_record(): void
    {
        Event::fake([SubscriberUpdateOrCreateEvent::class]);

        $this->loginAsSuperAdmin();

        /** @var $model Sip */
        $model = $this->sipBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setData([
            'is_insert_kamailio' => true
        ])->setSip($model)->create();

        $this->data['id'] = $model->id;

        $this->assertNotNull($model->employee);
        $this->assertNotEquals($model->number, data_get($this->data, 'number'));
        $this->assertNotEquals($model->password, data_get($this->data, 'password'));

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($this->data)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'id' => $model->id,
                        'number' => $model->number,
                    ],
                ]
            ])
        ;

        $model->refresh();

        $this->assertEquals($model->password, data_get($this->data, 'password'));

        Event::assertDispatched(fn (SubscriberUpdateOrCreateEvent $event) =>
            $event->getModel()->id === $employee->id
        );
        Event::assertListening(SubscriberUpdateOrCreateEvent::class, SubscriberUpdateOrInsertListener::class);
    }

    /** @test */
    public function success_update_with_employee_no_has_subscriber_record(): void
    {
        Event::fake([SubscriberUpdateOrCreateEvent::class]);

        $this->loginAsSuperAdmin();

        /** @var $model Sip */
        $model = $this->sipBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($model)->create();

        $this->data['id'] = $model->id;

        $this->assertNotNull($model->employee);
        $this->assertNotEquals($model->number, data_get($this->data, 'number'));
        $this->assertNotEquals($model->password, data_get($this->data, 'password'));

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($this->data)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'id' => $model->id,
                        'number' => $model->number,
                    ],
                ]
            ])
        ;

        $model->refresh();

        $this->assertEquals($model->password, data_get($this->data, 'password'));

        Event::assertNotDispatched(SubscriberUpdateOrCreateEvent::class);
    }

    /** @test */
    public function fail_wrong_password(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $model Sip */
        $model = $this->sipBuilder->create();

        $this->data['id'] = $model->id;
        $this->data['password'] = 'password';

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($this->data)
        ])
        ;

        $field = 'input.password';
        $this->assertResponseHasValidationMessage($res, $field,[
            __('validation.custom.password.password-rule', ['min' => Sip::MIN_LENGTH_PASSWORD])
        ]);
    }

    /** @test */
    public function not_auth(): void
    {
        /** @var $model Sip */
        $model = $this->sipBuilder->create();

        $this->data['id'] = $model->id;

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($this->data)
        ])
        ;

        $this->assertUnauthorized($res);
    }

    /** @test */
    public function not_perm(): void
    {
        $this->loginAsAdmin();

        /** @var $model Sip */
        $model = $this->sipBuilder->create();

        $this->data['id'] = $model->id;

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($this->data)
        ])
        ;

        $this->assertPermission($res);
    }

    protected function getQueryStr(array $data): string
    {
        return sprintf(
            '
            mutation {
                %s (
                    id: %s
                    input: {
                        number: "%s"
                        password: "%s"
                    },
                ) {
                    id
                    number
                    employee {
                        id
                    }
                    created_at
                    updated_at
                }
            }',
            self::MUTATION,
            data_get($data, 'id'),
            data_get($data, 'number'),
            data_get($data, 'password'),
        );
    }
}

