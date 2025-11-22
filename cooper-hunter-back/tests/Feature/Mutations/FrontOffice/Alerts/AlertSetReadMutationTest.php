<?php

namespace Tests\Feature\Mutations\FrontOffice\Alerts;

use App\Enums\Alerts\AlertOrderEnum;
use App\Enums\Orders\OrderStatusEnum;
use App\GraphQL\Mutations\Common\Alerts\BaseAlertSetReadMutation;
use App\Models\Alerts\Alert;
use App\Models\Alerts\AlertRecipient;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AlertSetReadMutationTest extends TestCase
{

    use DatabaseTransactions;
    use WithFaker;

    public function test_set_read_for_all_alerts(): void
    {
        $technician = $this->loginAsTechnicianWithRole();

        Alert::factory()
            ->forMember($technician)
            ->order(
                AlertOrderEnum::CHANGE_STATUS,
                [
                    'description' => [
                        'status' => OrderStatusEnum::getLocalizationKey() . '.' . OrderStatusEnum::PENDING_PAID
                    ]
                ]
            )
            ->count(2)
            ->create();

        $query = GraphQLQuery::mutation(BaseAlertSetReadMutation::NAME)
            ->make();

        $this->postGraphQL($query)
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
                'recipient_id' => $technician->getId(),
                'recipient_type' => $technician->getMorphType(),
                'is_read' => false
            ]
        );
    }

    public function test_set_read_for_one_alert(): void
    {
        $technician = $this->loginAsTechnicianWithRole();

        $alerts = Alert::factory()
            ->forMember($technician)
            ->order(
                AlertOrderEnum::CHANGE_STATUS,
                [
                    'description' => [
                        'status' => OrderStatusEnum::getLocalizationKey() . '.' . OrderStatusEnum::PENDING_PAID
                    ]
                ]
            )
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

        $this->postGraphQL($query)
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
                'recipient_id' => $technician->getId(),
                'recipient_type' => $technician->getMorphType(),
                'is_read' => false
            ]
        );

        $this->assertDatabaseHas(
            AlertRecipient::class,
            [
                'alert_id' => $alerts[0]->id,
                'recipient_id' => $technician->getId(),
                'recipient_type' => $technician->getMorphType(),
                'is_read' => true
            ]
        );
    }
}
