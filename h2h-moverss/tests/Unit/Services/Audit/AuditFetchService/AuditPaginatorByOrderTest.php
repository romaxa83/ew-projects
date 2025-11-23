<?php

namespace Tests\Unit\Services\Audit\AuditFetchService;

use App\Models\Order;
use App\Services\Audit\AuditFetchService;
use App\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\Builders\Audits\AuditBuilder;
use Tests\Builders\Orders\OrderBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\TestCase;

class AuditPaginatorByOrderTest extends TestCase
{
    use DatabaseTransactions;

    protected UserBuilder $userBuilder;
    protected OrderBuilder $orderBuilder;
    protected AuditBuilder $auditBuilder;

    protected AuditFetchService $service;

    public function setUp(): void
    {
        $this->userBuilder = resolve(UserBuilder::class);
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
        $order->audits()->delete();

        $this->auditBuilder->order($order)
            ->new_values(['test' => 1])->create();
        $this->auditBuilder->order($order)
            ->old_values(['test' => 1])->create();
        $this->auditBuilder->order($order)->new_values(['test' => 1])
            ->old_values(['test' => 1])->create();
        $this->auditBuilder->create();

        $res = $this->service->getAuditPaginatorByOrder($order);

        $this->assertTrue($res instanceof LengthAwarePaginator);

        $this->assertEquals(count($res->items()), 3);
    }

//    /** @test */
//    public function success_without_empty_data()
//    {
//        /** @var $order Order */
//        $order = $this->orderBuilder->create();
//        $order->audits()->delete();
//
//        $this->auditBuilder
//            ->new_values(['test' => 1])
//            ->order($order)->create();
//
//        $this->auditBuilder->order($order)->create();
//        $this->auditBuilder->order($order)->create();
//        $this->auditBuilder->create();
//
//        $res = $this->service->getAuditPaginatorByOrder($order);
//
//        $this->assertTrue($res instanceof LengthAwarePaginator);
//
//        $this->assertEquals(count($res->items()), 1);
//    }

    /** @test */
    public function success_with_user()
    {
        /** @var $order Order */
        $order = $this->orderBuilder->create();
        $order->audits()->delete();
        /** @var $user User */
        $user = $this->userBuilder->create();

        $this->auditBuilder
            ->user($user)
            ->new_values(['test' => 1])
            ->order($order)->create();
        $this->auditBuilder
            ->user($user)
            ->new_values(['test' => 1])
            ->order($order)->create();

        $this->auditBuilder
            ->new_values(['test' => 1])
            ->order($order)->create();
        $this->auditBuilder->create();

        $res = $this->service->getAuditPaginatorByOrder($order, ['user_id' => $user->id]);

        $this->assertTrue($res instanceof LengthAwarePaginator);

        $this->assertEquals(count($res->items()), 2);
    }

    /** @test */
    public function success_sort()
    {
        $date = CarbonImmutable::now();

        /** @var $order Order */
        $order = $this->orderBuilder->create();
        $order->audits()->delete();

        $rec_1 = $this->auditBuilder
            ->new_values(['test' => 1])
            ->order($order)
            ->created_at($date->subHours(4))
            ->create();
        $rec_2 = $this->auditBuilder
            ->new_values(['test' => 1])
            ->order($order)
            ->created_at($date->subHours(3))
            ->create();
        $rec_3 = $this->auditBuilder
            ->new_values(['test' => 1])
            ->order($order)
            ->created_at($date->subHours(2))
            ->create();

        $res = $this->service->getAuditPaginatorByOrder($order);

        $this->assertEquals($res->items()[0]->id, $rec_3->id);
        $this->assertEquals($res->items()[1]->id, $rec_2->id);
        $this->assertEquals($res->items()[2]->id, $rec_1->id);

        $res = $this->service->getAuditPaginatorByOrder($order, ['sort_type' => 'asc']);

        $this->assertEquals($res->items()[0]->id, $rec_1->id);
        $this->assertEquals($res->items()[1]->id, $rec_2->id);
        $this->assertEquals($res->items()[2]->id, $rec_3->id);
    }
}
