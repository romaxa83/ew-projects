<?php

namespace Tests\Feature\Mutations\BackOffice\Employees;

use App\Enums\Employees\Status;
use App\Events\Employees\EmployeeUpdatedEvent;
use App\GraphQL\Mutations\BackOffice;
use App\IPTelephony\Events\QueueMember\QueueMemberDeleteAndInsertEvent;
use App\IPTelephony\Events\QueueMember\QueueMemberUpdateEvent;
use App\IPTelephony\Events\Subscriber\SubscriberDeleteEvent;
use App\IPTelephony\Listeners\QueueMember\QueueMemberDeleteAndInsertListener;
use App\IPTelephony\Listeners\Subscriber\SubscriberUpdateOrInsertListener;
use App\Models\Departments\Department;
use App\Models\Employees\Employee;
use App\Models\Sips\Sip;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Tests\Builders\Admins\AdminBuilder;
use Tests\Builders\Departments\DepartmentBuilder;
use Tests\Builders\Employees\EmployeeBuilder;
use Tests\Builders\Kamailio\LocationBuilder;
use Tests\Builders\Sips\SipBuilder;
use Tests\TestCase;

class UpdateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    protected DepartmentBuilder $departmentBuilder;
    protected SipBuilder $sipBuilder;
    protected EmployeeBuilder $employeeBuilder;
    protected AdminBuilder $adminBuilder;
    protected LocationBuilder $locationBuilder;

    protected array $data;

    public const MUTATION = BackOffice\Employees\EmployeeUpdateMutation::NAME;

    public function setUp(): void
    {
        parent::setUp();

        $this->adminBuilder = resolve(AdminBuilder::class);
        $this->employeeBuilder = resolve(EmployeeBuilder::class);
        $this->departmentBuilder = resolve(DepartmentBuilder::class);
        $this->sipBuilder = resolve(SipBuilder::class);
        $this->locationBuilder = resolve(LocationBuilder::class);

        $this->data = [
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'email' => $this->faker->safeEmail,
        ];
    }

    /** @test */
    public function success_update_without_password(): void
    {
        Event::fake([
            EmployeeUpdatedEvent::class,
            QueueMemberDeleteAndInsertEvent::class
        ]);

        $this->loginAsSuperAdmin();

        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();

        /** @var $model Employee */
        $model = $this->employeeBuilder->create();

        $data = $this->data;
        $data['id'] = $model->id;
        $data['department_id'] = $department->id;
        $data['sip_id'] = $sip->id;

        $this->assertNotEquals($model->first_name, data_get($data, 'first_name'));
        $this->assertNotEquals($model->last_name, data_get($data, 'last_name'));
        $this->assertNotEquals($model->email, data_get($data, 'email'));
        $this->assertNotEquals($model->department_id, $department->id);
        $this->assertNull($model->sip);
        $this->assertFalse($model->status->isError());

        $pass = $model->password;

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'id' => $model->id,
                        'first_name' => data_get($data, 'first_name'),
                        'last_name' => data_get($data, 'last_name'),
                        'email' => data_get($data, 'email'),
                        'status' => Status::ERROR,
                        'department' => [
                            'id' => $department->id
                        ],
                        'sip' => [
                            'id' => $sip->id
                        ],
                    ],
                ]
            ])
        ;

        $model->refresh();

        $this->assertEquals($model->password, $pass);

        Event::assertDispatched(fn (EmployeeUpdatedEvent $event) =>
            $event->getModel()->id === $model->id
        );
        Event::assertListening(
            EmployeeUpdatedEvent::class,
            SubscriberUpdateOrInsertListener::class
        );

        Event::assertDispatched(fn (QueueMemberDeleteAndInsertEvent $event) =>
            $event->getModel()->id === $model->id
        );
        Event::assertListening(
            QueueMemberDeleteAndInsertEvent::class,
            QueueMemberDeleteAndInsertListener::class
        );
    }

    /** @test */
    public function success_update_with_password(): void
    {
        Event::fake([
            EmployeeUpdatedEvent::class,
            QueueMemberUpdateEvent::class
        ]);

        $this->loginAsSuperAdmin();

        /** @var $department Department */
        $department = $this->departmentBuilder->create();

        /** @var $model Employee */
        $model = $this->employeeBuilder->create();

        $data = $this->data;
        $data['id'] = $model->id;
        $data['first_name'] = $model->first_name;
        $data['last_name'] = $model->last_name;
        $data['department_id'] = $model->department_id;
        $data['password'] = 'newPassword1';

        $pass = $model->password;

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrWithPass($data)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'id' => $model->id,
                        'sip' => null
                    ],
                ]
            ])
        ;

        $model->refresh();

        $this->assertNotEquals($model->password, $pass);

        Event::assertNotDispatched(EmployeeUpdatedEvent::class);
        Event::assertNotDispatched(QueueMemberUpdateEvent::class);
    }

    /** @test */
    public function success_update_exist_sip(): void
    {
        Event::fake([EmployeeUpdatedEvent::class]);

        $this->loginAsSuperAdmin();

        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();

        $this->locationBuilder->setSip($sip)->create();

        /** @var $model Employee */
        $model = $this->employeeBuilder->setSip($sip)->create();

        $data = $this->data;
        $data['id'] = $model->id;
        $data['department_id'] = $department->id;
        $data['sip_id'] = $sip->id;

        $this->assertEquals($model->sip_id, $sip->id);
        $this->assertFalse($model->status->isError());

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'status' => $model->status,
                        'sip' => [
                            'id' => $sip->id
                        ],
                    ],
                ]
            ])
        ;
    }

    /** @test */
    public function success_update_sip(): void
    {
        Event::fake([EmployeeUpdatedEvent::class]);

        $this->loginAsSuperAdmin();

        /** @var $department Department */
        $department = $this->departmentBuilder->create();

        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        $sip_2 = $this->sipBuilder->create();

        /** @var $model Employee */
        $model = $this->employeeBuilder->setSip($sip)->create();

        $data = $this->data;
        $data['id'] = $model->id;
        $data['first_name'] = $model->first_name;
        $data['last_name'] = $model->last_name;
        $data['department_id'] = $department->id;
        $data['sip_id'] = $sip_2->id;

        $this->assertNotEquals($model->sip_id, $sip_2->id);
        $this->assertFalse($model->status->isError());

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'id' => $model->id,
                        'status' => Status::ERROR,
                        'sip' => [
                            'id' => $sip_2->id
                        ]
                    ],
                ]
            ])
        ;

        Event::assertDispatched(EmployeeUpdatedEvent::class);
    }

    /** @test */
    public function success_delete_sip(): void
    {
        Event::fake([
            EmployeeUpdatedEvent::class,
            SubscriberDeleteEvent::class
        ]);

        $this->loginAsSuperAdmin();

        /** @var $department Department */
        $department = $this->departmentBuilder->create();

        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        $sip_2 = $this->sipBuilder->create();

        /** @var $model Employee */
        $model = $this->employeeBuilder->setSip($sip)->create();

        $data = $this->data;
        $data['id'] = $model->id;
        $data['first_name'] = $model->first_name;
        $data['last_name'] = $model->last_name;
        $data['department_id'] = $department->id;
        $data['sip_id'] = null;

        $this->assertNotEquals($model->sip_id, $sip_2->id);

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStrWithoutSip($data)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'id' => $model->id,
                        'sip' => null
                    ],
                ]
            ])
        ;

        Event::assertNotDispatched(EmployeeUpdatedEvent::class);
        Event::assertDispatched(SubscriberDeleteEvent::class);
    }

    /** @test */
    public function fail_sip_attached_already(): void
    {
        $this->loginAsSuperAdmin();

        $this->employeeBuilder->create();

        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        $this->employeeBuilder->setSip($sip)->create();
        /** @var $model Employee */
        $model = $this->employeeBuilder->create();

        $data = $this->data;
        $data['id'] = $model->id;
        $data['department_id'] = $department->id;
        $data['sip_id'] = $sip->id;

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
        ;

        $this->assertResponseHasValidationMessage($res, 'input.sip_id', [
            __('validation.custom.sip.attached')
        ]);
    }

    /** @test */
    public function fail_not_unique_email_in_employee(): void
    {
        $this->loginAsSuperAdmin();

        $email = 'test@test.com';
        $this->employeeBuilder->setData(['email' => $email])->create();

        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        /** @var $model Employee */
        $model = $this->employeeBuilder->create();

        $data = $this->data;
        $data['id'] = $model->id;
        $data['department_id'] = $department->id;
        $data['sip_id'] = $sip->id;
        $data['email'] = $email;

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
        ;

        self::assertResponseHasValidationMessage($res, 'input.email', [
            __('validation.unique_email')
        ]);
    }

    /** @test */
    public function fail_not_unique_email_in_admin(): void
    {
        $this->loginAsSuperAdmin();

        $email = 'test@test.com';
        $this->adminBuilder->setData(['email' => $email])->create();

        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        /** @var $model Employee */
        $model = $this->employeeBuilder->create();

        $data = $this->data;
        $data['id'] = $model->id;
        $data['department_id'] = $department->id;
        $data['sip_id'] = $sip->id;
        $data['email'] = $email;

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
        ;

        self::assertResponseHasValidationMessage($res, 'input.email', [
            __('validation.unique_email')
        ]);
    }

    /** @test */
    public function not_auth(): void
    {
        /** @var $department Department */
        $department = $this->departmentBuilder->create();

        /** @var $model Employee */
        $model = $this->employeeBuilder->create();

        $data = $this->data;
        $data['id'] = $model->id;
        $data['department_id'] = $department->id;

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
        ;

        $this->assertUnauthorized($res);
    }

    /** @test */
    public function not_perm(): void
    {
        $this->loginAsAdmin();

        /** @var $department Department */
        $department = $this->departmentBuilder->create();

        /** @var $model Employee */
        $model = $this->employeeBuilder->create();

        $data = $this->data;
        $data['id'] = $model->id;
        $data['department_id'] = $department->id;

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
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
                        first_name: "%s"
                        last_name: "%s"
                        email: "%s"
                        department_id: "%s"
                        sip_id: "%s"
                    },
                ) {
                    id
                    status
                    first_name
                    last_name
                    email
                    department {
                        id
                    }
                    sip {
                        id
                    }
                }
            }',
            self::MUTATION,
            data_get($data, 'id'),
            data_get($data, 'first_name'),
            data_get($data, 'last_name'),
            data_get($data, 'email'),
            data_get($data, 'department_id'),
            data_get($data, 'sip_id'),
        );
    }

    protected function getQueryStrWithoutSip(array $data): string
    {
        return sprintf(
            '
            mutation {
                %s (
                    id: %s
                    input: {
                        first_name: "%s"
                        last_name: "%s"
                        email: "%s"
                        department_id: "%s"
                    },
                ) {
                    id
                    first_name
                    last_name
                    email
                    department {
                        id
                    }
                    sip {
                        id
                    }
                }
            }',
            self::MUTATION,
            data_get($data, 'id'),
            data_get($data, 'first_name'),
            data_get($data, 'last_name'),
            data_get($data, 'email'),
            data_get($data, 'department_id'),
        );
    }

    protected function getQueryStrWithPass(array $data): string
    {
        return sprintf(
            '
            mutation {
                %s (
                    id: %s
                    input: {
                        first_name: "%s"
                        last_name: "%s"
                        email: "%s"
                        department_id: "%s"
                        password: "%s"
                    },
                ) {
                    id
                    first_name
                    last_name
                    email
                    department {
                        id
                    }
                    sip {
                        id
                    }
                }
            }',
            self::MUTATION,
            data_get($data, 'id'),
            data_get($data, 'first_name'),
            data_get($data, 'last_name'),
            data_get($data, 'email'),
            data_get($data, 'department_id'),
            data_get($data, 'password'),
        );
    }
}

