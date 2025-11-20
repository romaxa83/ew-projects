<?php

namespace Tests\Feature\Mutations\BackOffice\Employees;

use App\Enums\Employees\Status;
use App\Events\Employees\EmployeeCreatedEvent;
use App\GraphQL\Mutations\BackOffice;
use App\IPTelephony\Listeners\QueueMember\QueueMemberInsertListener;
use App\IPTelephony\Listeners\Subscriber\SubscriberInsertListener;
use App\Listeners\Employees\SendCredentialsListener;
use App\Models\Departments\Department;
use App\Models\Employees\Employee;
use App\Models\Sips\Sip;
use App\Models\Users\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Tests\Builders\Admins\AdminBuilder;
use Tests\Builders\Departments\DepartmentBuilder;
use Tests\Builders\Employees\EmployeeBuilder;
use Tests\Builders\Kamailio\LocationBuilder;
use Tests\Builders\Sips\SipBuilder;
use Tests\TestCase;

class CreateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    protected EmployeeBuilder $employeeBuilder;
    protected AdminBuilder $adminBuilder;
    protected DepartmentBuilder $departmentBuilder;
    protected SipBuilder $sipBuilder;
    protected LocationBuilder $locationBuilder;

    protected array $data;

    public const MUTATION = BackOffice\Employees\EmployeeCreateMutation::NAME;

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
            'password' => 'Passssssword1',
        ];
    }

    /** @test */
    public function success_create_all_fields(): void
    {
        Event::fake([EmployeeCreatedEvent::class]);

        $this->loginAsSuperAdmin();

        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();

        $this->locationBuilder->setSip($sip)->create();

        $this->data['department_id'] = $department->id;
        $this->data['sip_id'] = $sip->id;

        $id = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($this->data)
        ])
            ->assertJsonStructure([
                'data' => [
                    self::MUTATION => [
                        'id',
                        'email_verified_at',
                        'created_at',
                        'updated_at',
                    ],
                ]
            ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'status' => Status::FREE,
                        'first_name' => data_get($this->data, 'first_name'),
                        'last_name' => data_get($this->data, 'last_name'),
                        'email' => data_get($this->data, 'email'),
                        'has_subscriber_record' => false,
                        'department' => [
                            'id' => $department->id
                        ],
                        'sip' => [
                            'id' => $sip->id
                        ]
                    ],
                ]
            ])
            ->json('data.'.self::MUTATION.'.id')
        ;

        /** @var $model Employee */
        $model = Employee::find($id);

        $this->assertNotNull($model->guid);
        $this->assertNotNull($model->email_verified_at);
        $this->assertNotNull($model->report);
        $this->assertTrue($model->role->isEmployee());

        Event::assertDispatched(fn (EmployeeCreatedEvent $event) =>
            $event->getModel()->id === (int)$id
            && $event->getDto()->password === data_get($this->data, 'password')
        );
        Event::assertListening(EmployeeCreatedEvent::class, SendCredentialsListener::class);
        Event::assertListening(EmployeeCreatedEvent::class, SubscriberInsertListener::class);
        Event::assertListening(EmployeeCreatedEvent::class, QueueMemberInsertListener::class);
    }

    /** @test */
    public function success_create_but_sip_not_to_location(): void
    {
        Event::fake([EmployeeCreatedEvent::class]);

        $this->loginAsSuperAdmin();

        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();

        $this->data['department_id'] = $department->id;
        $this->data['sip_id'] = $sip->id;

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($this->data)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'status' => Status::ERROR(),
                        'sip' => [
                            'id' => $sip->id
                        ]
                    ],
                ]
            ])
        ;
    }

    /** @test */
    public function fail_sip_attached_already(): void
    {
        Event::fake([EmployeeCreatedEvent::class]);

        $this->loginAsSuperAdmin();

        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)->create();

        $this->data['department_id'] = $department->id;
        $this->data['sip_id'] = $sip->id;

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($this->data)
        ])
        ;

        $this->assertResponseHasValidationMessage($res, 'input.sip_id', [
            __('validation.custom.sip.attached')
        ]);

        Event::assertNotDispatched(EmployeeCreatedEvent::class);
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

        $this->data['department_id'] = $department->id;
        $this->data['sip_id'] = $sip->id;
        $this->data['email'] = $email;

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($this->data)
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

        $this->data['department_id'] = $department->id;
        $this->data['sip_id'] = $sip->id;
        $this->data['email'] = $email;

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($this->data)
        ])
        ;

        self::assertResponseHasValidationMessage($res, 'input.email', [
            __('validation.unique_email')
        ]);
    }

    /** @test */
    public function fail_password_small(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();

        $this->data['department_id'] = $department->id;
        $this->data['sip_id'] = $sip->id;
        $this->data['password'] = 'Smal1';

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($this->data)
        ])
        ;

        self::assertResponseHasValidationMessage($res, 'input.password', [
            __('validation.custom.password.password-rule', ['min' => User::MIN_LENGTH_PASSWORD])
        ]);
    }

    /** @test */
    public function fail_password_without_number(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();

        $this->data['department_id'] = $department->id;
        $this->data['sip_id'] = $sip->id;
        $this->data['password'] = 'Smalllllllllllll';

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($this->data)
        ])
        ;

        self::assertResponseHasValidationMessage($res, 'input.password', [
            __('validation.custom.password.password-rule', ['min' => User::MIN_LENGTH_PASSWORD])
        ]);
    }

    /** @test */
    public function fail_password_without_latin_letter(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();

        $this->data['department_id'] = $department->id;
        $this->data['sip_id'] = $sip->id;
        $this->data['password'] = '22222222222222';

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($this->data)
        ])
        ;

        self::assertResponseHasValidationMessage($res, 'input.password', [
            __('validation.custom.password.password-rule', ['min' => User::MIN_LENGTH_PASSWORD])
        ]);
    }

    /** @test */
    public function not_auth(): void
    {
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();

        $this->data['department_id'] = $department->id;
        $this->data['sip_id'] = $sip->id;

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

        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();

        $this->data['department_id'] = $department->id;
        $this->data['sip_id'] = $sip->id;

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
                    input: {
                        first_name: "%s"
                        last_name: "%s"
                        email: "%s"
                        password: "%s"
                        department_id: "%s",
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
                    email_verified_at
                    created_at
                    updated_at
                    has_subscriber_record
                }
            }',
            self::MUTATION,
            data_get($data, 'first_name'),
            data_get($data, 'last_name'),
            data_get($data, 'email'),
            data_get($data, 'password'),
            data_get($data, 'department_id'),
            data_get($data, 'sip_id'),
        );
    }
}
