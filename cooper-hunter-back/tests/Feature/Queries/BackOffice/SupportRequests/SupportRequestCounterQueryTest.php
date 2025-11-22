<?php


namespace Feature\Queries\BackOffice\SupportRequests;

use App\GraphQL\Queries\BackOffice\SupportRequests\SupportRequestCounterQuery;
use App\Models\Admins\Admin;
use App\Permissions\SupportRequests\SupportRequestListPermission;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Models\SupportRequestCreateTrait;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class SupportRequestCounterQueryTest extends TestCase
{

    use DatabaseTransactions;
    use SupportRequestCreateTrait;
    use AdminManagerHelperTrait;

    private GraphQLQuery $query;

    private Admin $admin;

    public function setUp(): void
    {
        parent::setUp();

        $this->query = GraphQLQuery::query(SupportRequestCounterQuery::NAME)
            ->select(
                [
                    'new'
                ]
            );

        $this->admin = $this->loginByAdminManager([SupportRequestListPermission::KEY]);
    }

    public function test_get_counter(): void
    {
        $this->countSupportRequests(3)
            ->createSupportRequest();
        $this->postGraphQLBackOffice($this->query->make())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        SupportRequestCounterQuery::NAME => [
                            'new' => 3
                        ]
                    ]
                ]
            );
    }

    public function test_get_counter_with_one_answer(): void
    {
        $requests = $this->countSupportRequests(3)
            ->createSupportRequest();

        $requests[0]->messages()
            ->create(
                [
                    'sender_id' => $this->admin->id,
                    'sender_type' => Admin::MORPH_NAME,
                    'message' => $this->faker->text
                ]
            );

        $this->postGraphQLBackOffice($this->query->make())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        SupportRequestCounterQuery::NAME => [
                            'new' => 2
                        ]
                    ]
                ]
            );
    }

    public function test_get_counter_with_closed(): void
    {
        $requests = $this->countSupportRequests(4)
            ->createSupportRequest();

        $requests[0]->is_closed = true;
        $requests[0]->save();

        $this->postGraphQLBackOffice($this->query->make())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        SupportRequestCounterQuery::NAME => [
                            'new' => 3
                        ]
                    ]
                ]
            );
    }

}
