<?php

namespace Tests\Unit\Listeners\Employees;

use App\Dto\Employees\EmployeeDto;
use App\Events\Employees\EmployeeCreatedEvent;
use App\Listeners\Employees\SendCredentialsListener;
use App\Models\Employees\Employee;
use App\Notifications\Employees\SendCredentialsNotification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Tests\Builders\Departments\DepartmentBuilder;
use Tests\Builders\Employees\EmployeeBuilder;
use Tests\Builders\Sips\SipBuilder;
use Tests\TestCase;

class SendCredentialsListenerTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    protected DepartmentBuilder $departmentBuilder;
    protected SipBuilder $sipBuilder;
    protected EmployeeBuilder $employeeBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->departmentBuilder = resolve(DepartmentBuilder::class);
        $this->sipBuilder = resolve(SipBuilder::class);
        $this->employeeBuilder = resolve(EmployeeBuilder::class);
    }

    /** @test */
    public function success_send()
    {
        Notification::fake();

        /** @var $employee Employee */
        $employee = $this->employeeBuilder->create();
        $dto = EmployeeDto::byArgs([
            'first_name' => $employee->first_name,
            'last_name' => $employee->last_name,
            'email' => $employee->email->getValue(),
            'department_id' => $employee->department_id,
            'password' => 'password',
            'send_email' => true
        ]);

        $event = new EmployeeCreatedEvent($employee, $dto);
        $listener = resolve(SendCredentialsListener::class);
        $listener->handle($event);

        Notification::assertSentTo(new AnonymousNotifiable(), SendCredentialsNotification::class,
            function ($notification, $channels, $notifiable) use ($employee) {
                return $notifiable->routes['mail'] == $employee->email->getValue();
            }
        );
    }

    /** @test */
    public function not_send_no_field_send_email()
    {
        Notification::fake();

        /** @var $employee Employee */
        $employee = $this->employeeBuilder->create();
        $dto = EmployeeDto::byArgs([
            'first_name' => $employee->first_name,
            'last_name' => $employee->last_name,
            'email' => $employee->email->getValue(),
            'department_id' => $employee->department_id,
            'password' => 'password'
        ]);

        $event = new EmployeeCreatedEvent($employee, $dto);
        $listener = resolve(SendCredentialsListener::class);
        $listener->handle($event);

        Notification::assertNotSentTo(new AnonymousNotifiable(), SendCredentialsNotification::class,
            function ($notification, $channels, $notifiable) use ($employee) {
                return $notifiable->routes['mail'] == $employee->email->getValue();
            }
        );
    }

    /** @test */
    public function not_send_field_send_email_false()
    {
        Notification::fake();

        /** @var $employee Employee */
        $employee = $this->employeeBuilder->create();
        $dto = EmployeeDto::byArgs([
            'first_name' => $employee->first_name,
            'last_name' => $employee->last_name,
            'email' => $employee->email->getValue(),
            'department_id' => $employee->department_id,
            'send_email' => false
        ]);

        $event = new EmployeeCreatedEvent($employee, $dto);
        $listener = resolve(SendCredentialsListener::class);
        $listener->handle($event);

        Notification::assertNotSentTo(new AnonymousNotifiable(), SendCredentialsNotification::class,
            function ($notification, $channels, $notifiable) use ($employee) {
                return $notifiable->routes['mail'] == $employee->email->getValue();
            }
        );
    }

    /** @test */
    public function not_send_no_dto()
    {
        Notification::fake();

        /** @var $employee Employee */
        $employee = $this->employeeBuilder->create();

        $event = new EmployeeCreatedEvent($employee);
        $listener = resolve(SendCredentialsListener::class);
        $listener->handle($event);

        Notification::assertNotSentTo(new AnonymousNotifiable(), SendCredentialsNotification::class,
            function ($notification, $channels, $notifiable) use ($employee) {
                return $notifiable->routes['mail'] == $employee->email->getValue();
            }
        );
    }
}

