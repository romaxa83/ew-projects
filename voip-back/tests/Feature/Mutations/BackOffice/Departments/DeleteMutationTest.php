<?php

namespace Tests\Feature\Mutations\BackOffice\Departments;

use App\GraphQL\Mutations\BackOffice;
use App\IPTelephony\Services\Storage\Asterisk\QueueService;
use App\Models\Departments\Department;
use App\Models\Employees\Employee;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Tests\Builders\Departments\DepartmentBuilder;
use Tests\Builders\Employees\EmployeeBuilder;
use Tests\TestCase;

class DeleteMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    protected DepartmentBuilder $departmentBuilder;
    protected EmployeeBuilder $employeeBuilder;

    public const MUTATION = BackOffice\Departments\DepartmentDeleteMutation::NAME;

    public function setUp(): void
    {
        parent::setUp();

        $this->departmentBuilder = resolve(DepartmentBuilder::class);
        $this->employeeBuilder = resolve(EmployeeBuilder::class);
    }

    /** @test */
    public function success_delete(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $model Department */
        $model = $this->departmentBuilder->create();

        $mockQueueService = Mockery::mock(new QueueService);
        $this->app->instance(QueueService::class, $mockQueueService);
        $mockQueueService->shouldReceive('remove')
            ->once()
            ->andReturn(true);

        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($model->id)
        ])
            ->assertJson([
                'data' => [
                    self::MUTATION => true,
                ]
            ])
        ;

        $this->assertNull(Department::find($model->id));
    }

    /** @test */
    public function fail_exist_employee(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $model Department */
        $model = $this->departmentBuilder->create();

        /** @var $employee Employee */
        $this->employeeBuilder->setDepartment($model)->create();

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($model->id)
        ])
        ;

        $this->assertExceptionMessage($res, __('exceptions.department.cant_delete_exist_employee'));

        $this->assertNotNull(Department::find($model->id));
    }

    /** @test */
    public function fail_not_delete_to_asterisk(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $model Department */
        $model = $this->departmentBuilder->create();

        $mockQueueService = Mockery::mock(new QueueService);
        $this->app->instance(QueueService::class, $mockQueueService);
        $mockQueueService->shouldReceive('remove')
            ->once()
            ->andReturn(false);

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($model->id)
        ])
        ;

        $this->assertExceptionMessage($res, __('exceptions.asterisk.queue.cant_delete'));
    }

    /** @test */
    public function not_auth(): void
    {
        /** @var $model Department */
        $model = $this->departmentBuilder->create();

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($model->id)
        ])
        ;

        $this->assertUnauthorized($res);

        $this->assertNotNull(Department::find($model->id));
    }

    /** @test */
    public function not_perm(): void
    {
        $this->loginAsAdmin();

        /** @var $model Department */
        $model = $this->departmentBuilder->create();

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($model->id)
        ])
        ;

        $this->assertPermission($res);

        $this->assertNotNull(Department::find($model->id));
    }

    protected function getQueryStr(int $id): string
    {
        return sprintf(
            '
            mutation {
                %s (
                    id: %s
                )
            }',
            self::MUTATION,
            $id
        );
    }
}

