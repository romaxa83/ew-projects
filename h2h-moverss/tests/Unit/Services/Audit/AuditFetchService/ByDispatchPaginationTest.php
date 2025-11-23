<?php

namespace Tests\Unit\Services\Audit\AuditFetchService;

use App\Models\DispatchTruck;
use App\Models\Order\Work;
use App\Models\Truck\Truck;
use App\Services\Audit\AuditFetchService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Tests\Builders\Audits\AuditBuilder;
use Tests\Builders\Employees\DispatchEmployeeBuilder;
use Tests\Builders\Orders\WorkBuilder;
use Tests\Builders\Trucks\DispatchTruckBuilder;
use Tests\Builders\Trucks\TruckBuilder;
use Tests\TestCase;

class ByDispatchPaginationTest extends TestCase
{
    use DatabaseTransactions;

    protected WorkBuilder $workBuilder;
    protected TruckBuilder $truckBuilder;
    protected DispatchEmployeeBuilder $dispatchEmployeeBuilder;
    protected DispatchTruckBuilder $dispatchTruckBuilder;
    protected AuditBuilder $auditBuilder;

    protected AuditFetchService $service;

    public function setUp(): void
    {
        $this->dispatchEmployeeBuilder = resolve(DispatchEmployeeBuilder::class);
        $this->dispatchTruckBuilder = resolve(DispatchTruckBuilder::class);
        $this->workBuilder = resolve(WorkBuilder::class);
        $this->truckBuilder = resolve(TruckBuilder::class);
        $this->auditBuilder = resolve(AuditBuilder::class);
        $this->service = resolve(AuditFetchService::class);


        parent::setUp();
    }

    /** @test */
    public function success()
    {
        /** @var $work Work */
        $work = $this->workBuilder->create();
        /** @var $truck Truck */
        $truck = $this->truckBuilder->create();
        /** @var $model DispatchTruck */
        $model = $this->dispatchTruckBuilder
            ->create();
        $model->audits()->delete();

        $this->auditBuilder
            ->auditable($model)
            ->new_values([
                'truck_id' => $truck->id,
                'work_id' => $work->id,
                'id' => 27218,
            ])
            ->create();

        $res = $this->service->byDispatchPagination();

        $this->assertTrue($res['data'] instanceof AnonymousResourceCollection);

        $this->assertEquals($res['meta']['current_page'], 1);
        $this->assertEquals($res['meta']['from'], 1);
        $this->assertEquals($res['meta']['last_page'], 1);
        $this->assertEquals($res['meta']['per_page'], AuditFetchService::DEFAULT_PER_PAGE);
        $this->assertEquals($res['meta']['to'], 1);
        $this->assertEquals($res['meta']['total'], 1);
    }
}
