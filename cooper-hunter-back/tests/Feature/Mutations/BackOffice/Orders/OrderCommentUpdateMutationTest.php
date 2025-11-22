<?php


namespace Feature\Mutations\BackOffice\Orders;


use App\GraphQL\Mutations\BackOffice\Orders\OrderCommentUpdateMutation;
use App\Permissions\Orders\OrderUpdatePermission;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\Models\OrderCreateTrait;
use Tests\Traits\Models\ProjectCreateTrait;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class OrderCommentUpdateMutationTest extends TestCase
{

    use DatabaseTransactions;
    use WithFaker;
    use ProjectCreateTrait;
    use OrderCreateTrait;
    use AdminManagerHelperTrait;

    public function test_update_comment(): void
    {
        $this->loginByAdminManager([OrderUpdatePermission::KEY]);

        $order = $this->createPendingPaidOrder();

        $comment = $this->faker->text;

        $query = new GraphQLQuery(
            OrderCommentUpdateMutation::NAME,
            [
                'id' => $order->id,
                'comment' => $comment
            ],
            [
                'id',
                'comment'
            ]
        );

        $this->postGraphQLBackOffice($query->getMutation())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        OrderCommentUpdateMutation::NAME => [
                            'id' => (string)$order->id,
                            'comment' => $comment
                        ]
                    ]
                ]
            );
    }

    public function test_delete_comment(): void
    {
        $this->loginByAdminManager([OrderUpdatePermission::KEY]);

        $order = $this->createPendingPaidOrder(['comment' => $this->faker->text]);

        $query = new GraphQLQuery(
            OrderCommentUpdateMutation::NAME,
            [
                'id' => $order->id
            ],
            [
                'id',
                'comment'
            ]
        );

        $this->postGraphQLBackOffice($query->getMutation())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        OrderCommentUpdateMutation::NAME => [
                            'id' => (string)$order->id,
                            'comment' => null
                        ]
                    ]
                ]
            );
    }
}
