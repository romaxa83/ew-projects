<?php

namespace Tests\Feature\Mutations\BackOffice\Employees;

use App\Enums\Employees\Status;
use App\GraphQL\Mutations\BackOffice;
use App\IPTelephony\Events\QueueMember\QueueMemberPausedEvent;
use App\IPTelephony\Listeners\QueueMember\QueueMemberPausedListener;
use App\Models\Employees\Employee;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Tests\Builders\Admins\AdminBuilder;
use Tests\Builders\Employees\EmployeeBuilder;
use Tests\TestCase;

class ChangeStatusMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    protected EmployeeBuilder $employeeBuilder;
    protected AdminBuilder $adminBuilder;

    public const MUTATION = BackOffice\Employees\EmployeeChangeStatusMutation::NAME;

    public function setUp(): void
    {
        parent::setUp();

        $this->adminBuilder = resolve(AdminBuilder::class);
        $this->employeeBuilder = resolve(EmployeeBuilder::class);
    }

    /** @test */
    public function success_change_status_as_admin(): void
    {
        Event::fake([QueueMemberPausedEvent::class]);

        $this->loginAsSuperAdmin();

        /** @var $model Employee */
        $model = $this->employeeBuilder->create();

        $data['id'] = $model->id;
        $data['status'] = Status::TALK;

        $this->assertNotEquals($model->status, data_get($data, 'status'));

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'id' => $model->id,
                        'status' => data_get($data, 'status'),
                    ],
                ]
            ])
        ;

        Event::assertDispatched(fn (QueueMemberPausedEvent $event) =>
            $event->getModel()->id === $model->id
            && $event->paused() === false
        );
        Event::assertListening(QueueMemberPausedEvent::class, QueueMemberPausedListener::class);
    }

    /** @test */
    public function success_change_status_as_employee_self(): void
    {
        Event::fake([QueueMemberPausedEvent::class]);

        /** @var $model Employee */
        $model = $this->loginAsEmployee();

        $data['id'] = $model->id;
        $data['status'] = Status::PAUSE;

        $this->assertNotEquals($model->status, data_get($data, 'status'));

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'id' => $model->id,
                        'status' => data_get($data, 'status'),
                    ],
                ]
            ])
        ;

        Event::assertDispatched(fn (QueueMemberPausedEvent $event) =>
            $event->getModel()->id === $model->id
            && $event->paused() === true
        );
        Event::assertListening(QueueMemberPausedEvent::class, QueueMemberPausedListener::class);
    }

    /** @test */
    public function fail_change_status_as_employee_another(): void
    {
        $this->loginAsEmployee();
        /** @var $model Employee */
        $model = $this->employeeBuilder->create();

        $data['id'] = $model->id;
        $data['status'] = Status::PAUSE;

        $this->assertNotEquals($model->status, data_get($data, 'status'));

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($data)
        ])
        ;

        $this->assertPermission($res);
    }

    /** @test */
    public function not_auth(): void
    {
        /** @var $model Employee */
        $model = $this->employeeBuilder->create();

        $data['id'] = $model->id;
        $data['status'] = Status::FREE;

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

        /** @var $model Employee */
        $model = $this->employeeBuilder->create();

        $data['id'] = $model->id;
        $data['status'] = Status::FREE;

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
                    status: %s,
                ) {
                    id
                    status
                }
            }',
            self::MUTATION,
            data_get($data, 'id'),
            data_get($data, 'status'),
        );
    }
}


