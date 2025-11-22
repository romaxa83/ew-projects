<?php

namespace Tests\Feature\Queries\BackOffice\Dashboard;

use App\GraphQL\Queries\BackOffice\Dashboard\DashboardWidgetsQuery;
use App\Models\Faq\Question;
use App\Models\Orders\Order;
use App\Models\Support\SupportRequest;
use App\Models\Warranty\WarrantyRegistration;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DashboardWidgetsQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = DashboardWidgetsQuery::NAME;

    public function test_get_widgets_for_permitted_admin(): void
    {
        $this->loginAsSuperAdmin();

        $this->createWidgetsData();

        $query = $this->getQuery();

        $this->postGraphQLBackOffice($query)
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        self::QUERY => [
                            [
                                'title',
                                'value',
                                'section',
                                'type',
                            ],
                        ],
                    ],
                ],
            );
    }

    protected function createWidgetsData(): void
    {
        SupportRequest::factory()->create();
        SupportRequest::factory()->closed()->create();

        Order::factory()->create();
        Order::factory()->paid()->create();

        Question::factory()->create();
        Question::factory()->answered()->create();

        WarrantyRegistration::factory()->create();
        WarrantyRegistration::factory()->voided()->create();
    }

    public function getQuery(): array
    {
        return GraphQLQuery::query(self::QUERY)
            ->select(
                [
                    'title',
                    'value',
                    'section',
                    'type',
                ]
            )
            ->make();
    }

    public function test_not_permitted_admin_cannot_view_widgets(): void
    {
        $this->loginAsAdmin();

        $this->createWidgetsData();

        $query = $this->getQuery();

        $this->postGraphQLBackOffice($query)
            ->assertOk()
            ->assertJsonCount(0, 'data.' . self::QUERY);
    }

    public function test_unauthorized(): void
    {
        $this->createWidgetsData();

        $query = $this->getQuery();

        $this->assertServerError($this->postGraphQLBackOffice($query), 'Unauthorized');
    }
}