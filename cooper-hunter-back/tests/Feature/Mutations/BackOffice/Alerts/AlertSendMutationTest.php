<?php


namespace Tests\Feature\Mutations\BackOffice\Alerts;


use App\Enums\Users\UserMorphEnum;
use App\GraphQL\Mutations\BackOffice\Alerts\AlertSendMutation;
use App\Models\Alerts\Alert;
use App\Models\Alerts\AlertRecipient;
use App\Models\Technicians\Technician;
use App\Models\Users\User;
use App\Notifications\Alerts\AlertFcmNotification;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class AlertSendMutationTest extends TestCase
{
    use DatabaseTransactions;
    use AdminManagerHelperTrait;
    use WithFaker;

    public function test_send_custom_alert(): void
    {
        $this->loginAsSuperAdmin();

        Notification::fake();

        $user = User::factory()
            ->create();
        $technician = Technician::factory()
            ->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(AlertSendMutation::NAME)
                ->args(
                    [
                        'input' => [
                            'recipients' => [
                                [
                                    'id' => $user->id,
                                    'type' => UserMorphEnum::USER()
                                ],
                                [
                                    'id' => $technician->id,
                                    'type' => UserMorphEnum::TECHNICIAN()
                                ],
                            ],
                            'title' => $this->faker->text,
                            'description' => $this->faker->text
                        ]
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        AlertSendMutation::NAME => true
                    ]
                ]
            );

        $this->assertDatabaseHas(
            AlertRecipient::class,
            [
                'recipient_id' => $user->id,
                'recipient_type' => UserMorphEnum::USER
            ]
        );

        $this->assertDatabaseHas(
            AlertRecipient::class,
            [
                'recipient_id' => $technician->id,
                'recipient_type' => UserMorphEnum::TECHNICIAN
            ]
        );

        $this->assertDatabaseHas(
            Alert::class,
            [
                'model_id' => $user->id,
                'model_type' => UserMorphEnum::USER
            ]
        );

        $this->assertDatabaseHas(
            Alert::class,
            [
                'model_id' => $technician->id,
                'model_type' => UserMorphEnum::TECHNICIAN
            ]
        );

        Notification::assertSentTo($user, AlertFcmNotification::class);
        Notification::assertSentTo($technician, AlertFcmNotification::class);
    }
}
