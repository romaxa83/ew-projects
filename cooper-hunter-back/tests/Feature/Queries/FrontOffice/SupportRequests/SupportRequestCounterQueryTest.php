<?php


namespace Feature\Queries\FrontOffice\SupportRequests;

use App\GraphQL\Queries\FrontOffice\SupportRequests\SupportRequestCounterQuery;
use App\Models\Admins\Admin;
use App\Models\Technicians\Technician;
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

    private Technician $technician;

    public function setUp(): void
    {
        parent::setUp();

        $this->query = GraphQLQuery::query(SupportRequestCounterQuery::NAME)
            ->select(
                [
                    'new_messages'
                ]
            );

        $this->admin = Admin::factory()
            ->create();

        $this->technician = $this->loginAsTechnicianWithRole();
    }

    public function test_get_counter(): void
    {
        $this->countSupportRequests(3)
            ->createSupportRequest($this->technician);

        $this->postGraphQL($this->query->make())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        SupportRequestCounterQuery::NAME => [
                            'new_messages' => 0
                        ]
                    ]
                ]
            );
    }

    public function test_get_counter_with_one_answer(): void
    {
        $requests = $this->countSupportRequests(3)
            ->createSupportRequest($this->technician);

        $requests[0]->messages()
            ->create(
                [
                    'sender_id' => $this->admin->id,
                    'sender_type' => Admin::MORPH_NAME,
                    'message' => $this->faker->text
                ]
            );

        $this->postGraphQL($this->query->make())
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        SupportRequestCounterQuery::NAME => [
                            'new_messages' => 1
                        ]
                    ]
                ]
            );
    }

}
