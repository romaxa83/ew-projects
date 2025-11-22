<?php


namespace Feature\Queries\BackOffice\Orders;


use App\GraphQL\Queries\BackOffice\Orders\OrderCounterQuery;
use App\Permissions\Orders\OrderListPermission;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\Models\OrderCreateTrait;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class OrderCounterQueryTest extends TestCase
{

    use DatabaseTransactions;
    use WithFaker;
    use OrderCreateTrait;
    use AdminManagerHelperTrait;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginByAdminManager([OrderListPermission::KEY]);
    }

    public function test_get_all_counters(): void
    {
        $this->manyOrder(3)
            ->createAllStatusesOrder();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(OrderCounterQuery::NAME)
                ->select(
                    [
                        'created',
                        'pending_paid',
                        'paid',
                        'shipped',
                        'canceled',
                        'total'
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        OrderCounterQuery::NAME => [
                            'created' => 3,
                            'pending_paid' => 3,
                            'paid' => 3,
                            'shipped' => 3,
                            'canceled' => 3,
                            'total' => 15,
                        ]
                    ]
                ]
            );
    }
}
