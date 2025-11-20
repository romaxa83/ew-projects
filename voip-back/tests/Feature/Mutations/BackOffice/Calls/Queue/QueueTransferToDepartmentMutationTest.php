<?php

namespace Tests\Feature\Mutations\BackOffice\Calls\Queue;

use App\GraphQL\Mutations\BackOffice;
use App\PAMI\Service\SendActionService;
use App\Services\Calls\QueueService;
use App\Models\Calls\Queue;
use App\Models\Departments\Department;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Tests\Builders\Calls\QueueBuilder;
use Tests\Builders\Departments\DepartmentBuilder;
use Tests\Builders\Employees\EmployeeBuilder;
use Tests\Builders\Sips\SipBuilder;
use Tests\TestCase;

class QueueTransferToDepartmentMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    protected QueueBuilder $queueBuilder;
    protected EmployeeBuilder $employeeBuilder;
    protected DepartmentBuilder $departmentBuilder;
    protected SipBuilder $sipBuilder;

    protected array $data;

    public const MUTATION = BackOffice\Calls\Queue\QueueTransferToDepartmentMutation::NAME;

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

        /** @var $department Department */
        $department = $this->departmentBuilder->setData(['num' => '89898'])->create();
        /** @var $queue Queue */
        $queue = $this->queueBuilder
            ->setConnectedAt(CarbonImmutable::now())
            ->setCalledAt(CarbonImmutable::now())
            ->create();

        $mockQueueService = Mockery::mock(new QueueService);
        $this->app->instance(SendActionService::class, $mockQueueService);
        $mockQueueService->shouldReceive('QueueRedirect')
            ->once()
            ->andReturn(true);

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr([
                'id' => $queue->id,
                'department_id' => $department->id,
            ])
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => true,
                ]
            ])
        ;

        $queue->refresh();

        $this->assertNull($queue->connected_at);
        $this->assertNull($queue->called_at);
    }

    /** @test */
    public function success_transfer_as_employee(): void
    {
        $this->loginAsEmployee();

        /** @var $department Department */
        $department = $this->departmentBuilder->create();

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
                'department_id' => $department->id,
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
        /** @var $d_1 Department */
        $d_1 = $this->departmentBuilder->create();
        $d_2 = $this->departmentBuilder->setData(['active' => false])->create();

        /** @var $d_1 Department */
        $e_1 = $this->employeeBuilder->setDepartment($d_2)->create();

        $this->loginAsEmployee($e_1);

        /** @var $queue Queue */
        $queue = $this->queueBuilder->create();

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr([
                'id' => $queue->id,
                'department_id' => $d_1->id,
            ])
        ])
        ;

        $this->assertExceptionMessage($res, __('exceptions.employee.can\'t_this_action'));
    }

    protected function getQueryStr(array $data): string
    {
        return sprintf(
            '
            mutation {
                %s (
                    id: %s,
                    department_id: %s
                )
            }',
            self::MUTATION,
            data_get($data, 'id'),
            data_get($data, 'department_id'),
        );
    }
}


