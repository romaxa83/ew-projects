<?php

namespace Tests\Feature\Mutations\BackOffice\Employees;

use App\GraphQL\Mutations\BackOffice;
use App\IPTelephony\Services\Storage\Asterisk\QueueMemberService;
use App\IPTelephony\Services\Storage\Kamailio\SubscriberService;
use App\Models\Employees\Employee;
use App\Models\Reports\Report;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Tests\Builders\Employees\EmployeeBuilder;
use Tests\Builders\Reports\ReportBuilder;
use Tests\TestCase;

class DeleteMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    protected EmployeeBuilder $employeeBuilder;
    protected ReportBuilder $reportBuilder;

    public const MUTATION = BackOffice\Employees\EmployeeDeleteMutation::NAME;

    public function setUp(): void
    {
        parent::setUp();

        $this->employeeBuilder = resolve(EmployeeBuilder::class);
        $this->reportBuilder = resolve(ReportBuilder::class);
    }

    /** @test */
    public function success_delete(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $model Employee */
        $model = $this->employeeBuilder->create();
        /** @var $report Report */
        $report = $this->reportBuilder->setEmployee($model)->create();

        $mockSubscriberService = Mockery::mock(new SubscriberService);
        $this->app->instance(SubscriberService::class, $mockSubscriberService);
        $mockSubscriberService->shouldReceive('remove')
            ->once()
            ->andReturn(true);

        $mockQueueMemberService = Mockery::mock(new QueueMemberService());
        $this->app->instance(QueueMemberService::class, $mockQueueMemberService);
        $mockQueueMemberService->shouldReceive('remove')
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

        $this->assertNull(Employee::find($model->id));
//        $this->assertNull(Report::query()->where('employee_id', $model->id)->first());
    }

    /** @test */
    public function fail_not_delete_to_kamailio(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $model Employee */
        $model = $this->employeeBuilder->create();

        $mockSubscriberService = Mockery::mock(new SubscriberService);
        $this->app->instance(SubscriberService::class, $mockSubscriberService);
        $mockSubscriberService->shouldReceive('remove')
            ->once()
            ->andReturn(false);

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($model->id)
        ])
        ;

        $this->assertExceptionMessage($res, __('exceptions.kamailio.cant_delete_subscriber'));
    }

    /** @test */
    public function fail_not_delete_to_queue_member(): void
    {
        $this->loginAsSuperAdmin();

        /** @var $model Employee */
        $model = $this->employeeBuilder->create();

        $mockSubscriberService = Mockery::mock(new SubscriberService);
        $this->app->instance(SubscriberService::class, $mockSubscriberService);
        $mockSubscriberService->shouldReceive('remove')
            ->once()
            ->andReturn(true);

        $mockQueueMemberService = Mockery::mock(new QueueMemberService());
        $this->app->instance(QueueMemberService::class, $mockQueueMemberService);
        $mockQueueMemberService->shouldReceive('remove')
            ->once()
            ->andReturn(false);

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($model->id)
        ])
        ;

        $this->assertExceptionMessage($res, __('exceptions.asterisk.queue_member.cant_delete'));
    }

    /** @test */
    public function not_auth(): void
    {
        /** @var $model Employee */
        $model = $this->employeeBuilder->create();

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($model->id)
        ])
        ;

        $this->assertUnauthorized($res);

        $this->assertNotNull(Employee::find($model->id));
    }

    /** @test */
    public function not_perm(): void
    {
        $this->loginAsAdmin();

        /** @var $model Employee */
        $model = $this->employeeBuilder->create();

        $res = $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($model->id)
        ])
        ;

        $this->assertPermission($res);

        $this->assertNotNull(Employee::find($model->id));
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
