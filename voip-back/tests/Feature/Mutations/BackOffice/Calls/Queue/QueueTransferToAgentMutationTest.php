<?php

namespace Tests\Feature\Mutations\BackOffice\Calls\Queue;

use App\GraphQL\Mutations\BackOffice;
use App\PAMI\Service\SendActionService;
use App\Services\Calls\QueueService;
use App\Models\Calls\Queue;
use App\Models\Departments\Department;
use App\Models\Employees\Employee;
use App\Models\Sips\Sip;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Tests\Builders\Calls\QueueBuilder;
use Tests\Builders\Departments\DepartmentBuilder;
use Tests\Builders\Employees\EmployeeBuilder;
use Tests\Builders\Sips\SipBuilder;
use Tests\TestCase;

class QueueTransferToAgentMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    protected QueueBuilder $queueBuilder;
    protected EmployeeBuilder $employeeBuilder;
    protected DepartmentBuilder $departmentBuilder;
    protected SipBuilder $sipBuilder;

    protected array $data;

    public const MUTATION = BackOffice\Calls\Queue\QueueTransferToAgentMutation::NAME;

    public function setUp(): void
    {
        parent::setUp();

        $this->queueBuilder = resolve(QueueBuilder::class);
        $this->employeeBuilder = resolve(EmployeeBuilder::class);
        $this->departmentBuilder = resolve(DepartmentBuilder::class);
        $this->sipBuilder = resolve(SipBuilder::class);
    }

    /** @test */
    public function success_transfer_as_admin(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();
        /** @var $queue Queue */
        $queue = $this->queueBuilder
            ->setConnectedAt(CarbonImmutable::now())
            ->setCalledAt(CarbonImmutable::now())
            ->create();

        $this->assertTrue($queue->status->isWait());
        $this->assertNull($queue->employee_id);

        $mockQueueService = Mockery::mock(new QueueService);
        $this->app->instance(SendActionService::class, $mockQueueService);
        $mockQueueService->shouldReceive('QueueRedirect')
            ->once()
            ->andReturn(true);

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr([
                'id' => $queue->id,
                'employee_id' => $employee->id,
            ])
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => true,
                ]
            ])
        ;

        $queue->refresh();

        $this->assertTrue($queue->status->isCancel());
        $this->assertEquals($queue->employee_id, $employee->id);
        $this->assertEquals($queue->connected_num, $employee->sip->number);
        $this->assertEquals($queue->connected_name, $employee->getName());
        $this->assertNull($queue->connected_at);
        $this->assertNull($queue->called_at);
    }

    /** @test */
    public function fail_transfer_as_admin(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();
        /** @var $queue Queue */
        $queue = $this->queueBuilder
            ->setConnectedAt(CarbonImmutable::now())
            ->setCalledAt(CarbonImmutable::now())
            ->create();

        $this->assertTrue($queue->status->isWait());

        $mockQueueService = Mockery::mock(new QueueService);
        $this->app->instance(SendActionService::class, $mockQueueService);
        $mockQueueService->shouldReceive('QueueRedirect')
            ->once()
            ->andReturn(false);

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr([
                'id' => $queue->id,
                'employee_id' => $employee->id,
            ])
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => false,
                ]
            ])
        ;

        $queue->refresh();

        $this->assertTrue($queue->status->isWait());
    }

    /** @test */
    public function success_transfer_as_employee(): void
    {
        $this->loginAsEmployee();

        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->setSip($sip)
            ->setDepartment($department)->create();
        /** @var $queue Queue */
        $queue = $this->queueBuilder->create();

        $mockQueueService = Mockery::mock(new QueueService);
        $this->app->instance(SendActionService::class, $mockQueueService);
        $mockQueueService->shouldReceive('QueueRedirect')
            ->once()
            ->andReturn(true);

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr([
                'id' => $queue->id,
                'employee_id' => $employee->id,
            ])
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => true,
                ]
            ])
        ;
    }

    /** @test */
    public function fail_transfer_as_employee_not_active_department(): void
    {
        /** @var $s_1 Sip */
        $s_1 = $this->sipBuilder->create();
        $s_2 = $this->sipBuilder->create();
        /** @var $d_1 Department */
        $d_1 = $this->departmentBuilder->create();
        $d_2 = $this->departmentBuilder->setData(['active' => false])->create();
        /** @var $e_1 Employee */
        $e_1 = $this->employeeBuilder->setSip($s_1)->setDepartment($d_1)->create();
        $e_2 = $this->employeeBuilder->setSip($s_2)->setDepartment($d_2)->create();

        $this->loginAsEmployee($e_2);

        /** @var $queue Queue */
        $queue = $this->queueBuilder->create();

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr([
                'id' => $queue->id,
                'employee_id' => $e_1->id,
            ])
        ])
        ;

        $this->assertExceptionMessage($res, __('exceptions.employee.can\'t_this_action'));
    }

    /** @test */
    public function fail_transfer_employee_not_has_sip(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $department Department */
        $department = $this->departmentBuilder->create();
        /** @var $employee Employee */
        $employee = $this->employeeBuilder
            ->setDepartment($department)->create();
        /** @var $queue Queue */
        $queue = $this->queueBuilder->create();

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr([
                'id' => $queue->id,
                'employee_id' => $employee->id,
            ])
        ])
        ;

        $this->assertExceptionMessage($res, __('exceptions.employee.has_not_sip'));
    }

    /** @test */
    public function not_auth(): void
    {
        /** @var $employee Employee */
        $employee = $this->employeeBuilder->create();
        /** @var $queue Queue */
        $queue = $this->queueBuilder->create();

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr([
                'id' => $queue->id,
                'employee_id' => $employee->id,
            ])
        ])
        ;

        $this->assertUnauthorized($res);
    }

    /** @test */
    public function not_perm(): void
    {
        $this->loginAsAdmin();

        /** @var $employee Employee */
        $employee = $this->employeeBuilder->create();
        /** @var $queue Queue */
        $queue = $this->queueBuilder->create();

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr([
                'id' => $queue->id,
                'employee_id' => $employee->id,
            ])
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
                    id: %s,
                    employee_id: %s
                )
            }',
            self::MUTATION,
            data_get($data, 'id'),
            data_get($data, 'employee_id'),
        );
    }
}

