<?php

namespace Tests\Unit\Services\Audit\AuditFetchService;

use App\Models\Order;
use App\Services\Audit\AuditFetchService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Tests\Builders\Audits\AuditBuilder;
use Tests\Builders\Orders\OrderBuilder;
use Tests\TestCase;

class ByOrderListTest extends TestCase
{
    use DatabaseTransactions;

    protected OrderBuilder $orderBuilder;
    protected AuditBuilder $auditBuilder;

    protected AuditFetchService $service;

    public function setUp(): void
    {
        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->auditBuilder = resolve(AuditBuilder::class);
        $this->service = resolve(AuditFetchService::class);


        parent::setUp();
    }

    /** @test */
    public function success_only_order()
    {
        /** @var $order Order */
        $order = $this->orderBuilder->create();

        $res = $this->service->byOrderList($order);

        $this->assertTrue($res['data'] instanceof AnonymousResourceCollection);
    }
}
