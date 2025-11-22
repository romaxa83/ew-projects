<?php

namespace Feature\Http\Api\V1\Orders\BS\Report;

use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Orders\BS\OrderBuilder;
use Tests\TestCase;

class TotalTest extends TestCase
{
    use DatabaseTransactions;

    protected OrderBuilder $orderBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->orderBuilder = resolve(OrderBuilder::class);
    }

    /** @test */
    public function success_get_data()
    {
        $this->loginUserAsSuperAdmin();

        $now = CarbonImmutable::now();

        // with current due
        $m_1 = $this->orderBuilder
            ->due_date($now->addDay())
            ->total_amount(100)
            ->paid_amount(50)
            ->debt_amount(50)
            ->profit(10)
            ->create();
        // with past due
        $m_2 = $this->orderBuilder
            ->due_date($now->addDays(-1))
            ->total_amount(100)
            ->paid_amount(50)
            ->debt_amount(50)
            ->profit(10)
            ->create();
        // paid
        $m_3 = $this->orderBuilder
            ->due_date($now->addDays(-1))
            ->total_amount(100)
            ->paid_amount(100)
            ->debt_amount(0)
            ->profit(10)
            ->create();
        // with current due
        $m_4 = $this->orderBuilder
            ->due_date($now->addDay())
            ->total_amount(100)
            ->paid_amount(70)
            ->debt_amount(30)
            ->create();
        // with past due
        $m_5 = $this->orderBuilder
            ->due_date($now->addDays(-1))
            ->total_amount(100)
            ->paid_amount(40)
            ->debt_amount(60)
            ->create();

        $this->getJson(route('api.v1.orders.bs.report-total'))
            ->assertJson([
                'data' => [
                   'total_due' => 190,
                   'current_due' => 80,
                   'past_due' => 110,
                   'total_amount' => 500,
                   'total_profit' => 30,
                ],
            ])
        ;
    }

    /** @test */
    public function success_empty()
    {
        $this->loginUserAsSuperAdmin();

        $this->getJson(route('api.v1.orders.bs.report-total'))
            ->assertJson([
                'data' => [
                    'total_due' => 0,
                    'current_due' => 0,
                    'past_due' => 0,
                    'total_amount' => 0,
                    'total_profit' => 0,
                ],
            ])
        ;
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        $res = $this->getJson(route('api.v1.orders.bs.report-total'));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        $res = $this->getJson(route('api.v1.orders.bs.report-total'));

        self::assertUnauthenticatedMessage($res);
    }
}
