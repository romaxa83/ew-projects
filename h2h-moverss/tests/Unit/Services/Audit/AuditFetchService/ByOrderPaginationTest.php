<?php

namespace Tests\Unit\Services\Audit\AuditFetchService;

use App\Models\Audit;
use App\Models\Division;
use App\Models\Order;
use App\Services\Audit\AuditFetchService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Tests\Builders\Audits\AuditBuilder;
use Tests\Builders\Divisions\DivisionBuilder;
use Tests\Builders\Orders\OrderBuilder;
use Tests\TestCase;

class ByOrderPaginationTest extends TestCase
{
    use DatabaseTransactions;

    protected OrderBuilder $orderBuilder;
    protected DivisionBuilder $divisionBuilder;
    protected AuditBuilder $auditBuilder;

    protected AuditFetchService $service;

    public function setUp(): void
    {
        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->divisionBuilder = resolve(DivisionBuilder::class);
        $this->auditBuilder = resolve(AuditBuilder::class);
        $this->service = resolve(AuditFetchService::class);


        parent::setUp();
    }

    /** @test */
    public function success_only_order()
    {
        /** @var $order Order */
        $order = $this->orderBuilder->create();

        $res = $this->service->byOrderPagination($order);

        $this->assertTrue($res['data'] instanceof AnonymousResourceCollection);

        $this->assertEquals($res['meta']['current_page'], 1);
        $this->assertEquals($res['meta']['from'], 1);
        $this->assertEquals($res['meta']['last_page'], 1);
        $this->assertEquals($res['meta']['per_page'], AuditFetchService::DEFAULT_PER_PAGE);
        $this->assertEquals($res['meta']['to'], 1);
        $this->assertEquals($res['meta']['total'], 1);
    }

    /** @test */
    public function success_delete_duplicate()
    {
        /** @var $division Division */
        $division = $this->divisionBuilder->create();

        /** @var $order Order */
        $order = $this->orderBuilder->create();
        $order->audits()->delete();

        /** @var $audit Audit */
        $audit_1 = $this->auditBuilder
            ->auditable($order)
            ->order($order)
            ->event(Audit::EVENT_CREATED)
            ->new_values([
                'division_id' => $division->id,
            ])
            ->create();
        /** @var $audit Audit */
        $audit_2 = $this->auditBuilder
            ->auditable($order)
            ->order($order)
            ->event(Audit::EVENT_CREATED)
            ->new_values([
                'division_id' => $division->id,
            ])
            ->create();

        $res = $this->service->byOrderPagination($order);

        $this->assertEquals($res['data']->count(), 1);
    }
}
