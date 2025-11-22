<?php


namespace Feature\Mutations\BackOffice\Alerts;


use App\Enums\Alerts\AlertOrderEnum;
use App\GraphQL\Mutations\Common\Alerts\BaseAlertSetReadMutation;
use App\Models\Alerts\Alert;
use App\Models\Alerts\AlertRecipient;
use App\Permissions\Alerts\AlertSetReadPermission;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class AlertSetReadMutationTest extends TestCase
{

    use DatabaseTransactions;
    use WithFaker;
    use AdminManagerHelperTrait;

    public function test_set_read_for_all_alerts(): void
    {
        $admin = $this->loginByAdminManager([AlertSetReadPermission::KEY]);

        Alert::factory()
            ->forAdmin($admin)
            ->order(AlertOrderEnum::CREATE, ['description' => ['email' => $this->faker->email]])
            ->count(2)
            ->create();

        $query = GraphQLQuery::mutation(BaseAlertSetReadMutation::NAME)
            ->make();

        $this->postGraphQLBackOffice($query)
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        BaseAlertSetReadMutation::NAME => true
                    ]
                ]
            );

        $this->assertDatabaseMissing(
            AlertRecipient::class,
            [
                'recipient_id' => $admin->getId(),
                'recipient_type' => $admin->getMorphType(),
                'is_read' => false
            ]
        );
    }

    public function test_set_read_for_one_alert(): void
    {
        $admin = $this->loginByAdminManager([AlertSetReadPermission::KEY]);

        $alerts = Alert::factory()
            ->forAdmin($admin)
            ->order(AlertOrderEnum::CREATE, ['description' => ['email' => $this->faker->email]])
            ->count(2)
            ->create();

        $query = GraphQLQuery::mutation(BaseAlertSetReadMutation::NAME)
            ->args(
                [
                    'ids' => [
                        $alerts[0]->id
                    ]
                ]
            )
            ->make();

        $this->postGraphQLBackOffice($query)
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        BaseAlertSetReadMutation::NAME => true
                    ]
                ]
            );

        $this->assertDatabaseHas(
            AlertRecipient::class,
            [
                'alert_id' => $alerts[1]->id,
                'recipient_id' => $admin->getId(),
                'recipient_type' => $admin->getMorphType(),
                'is_read' => false
            ]
        );

        $this->assertDatabaseHas(
            AlertRecipient::class,
            [
                'alert_id' => $alerts[0]->id,
                'recipient_id' => $admin->getId(),
                'recipient_type' => $admin->getMorphType(),
                'is_read' => true
            ]
        );
    }
}
