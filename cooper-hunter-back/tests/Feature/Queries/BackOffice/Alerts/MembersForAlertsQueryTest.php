<?php

namespace Tests\Feature\Queries\BackOffice\Alerts;

use App\Enums\Users\UserMorphEnum;
use App\GraphQL\Queries\BackOffice\Alerts\MembersForAlertQuery;
use App\Models\Technicians\Technician;
use App\Models\Users\User;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Dealers\DealerBuilder;
use Tests\TestCase;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class MembersForAlertsQueryTest extends TestCase
{
    use DatabaseTransactions;
    use AdminManagerHelperTrait;

    protected DealerBuilder $dealerBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->dealerBuilder = resolve(DealerBuilder::class);
    }

    public function test_get_members(): void
    {
        $this->loginAsSuperAdmin();

        $users = User::factory()
            ->count(3)
            ->create();

//        $dealers = Collection::make([
//            $this->dealerBuilder->asRegister()->create(),
//            $this->dealerBuilder->asRegister()->create(),
//        ]);

        $technicians = Technician::factory()
            ->count(3)
            ->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(MembersForAlertQuery::NAME)
                ->select([
                    'data' => [
                        'id',
                        'first_name',
                        'last_name',
                        'email',
                        'phone',
                        'type',
                        'created_at'
                    ]
                ])
                ->make()
        )
            ->assertOk()
            ->assertJsonStructure([
                'data' => [
                    MembersForAlertQuery::NAME => [
                        'data' => [
                            '*' => [
                                'id',
                                'first_name',
                                'last_name',
                                'email',
                                'phone',
                                'type',
                                'created_at'
                            ]
                        ]
                    ]
                ]
            ])
            ->assertJsonCount(6, 'data.' . MembersForAlertQuery::NAME . '.data')
            ->assertJson([
                'data' => [
                    MembersForAlertQuery::NAME => [
                        'data' => $users
                            ->map(
                                fn(User $user) => [
                                    'id' => $user->id,
                                    'first_name' => $user->first_name,
                                    'last_name' => $user->last_name,
                                    'email' => $user->email,
                                    'phone' => $user->phone,
                                    'type' => UserMorphEnum::USER,
                                    'created_at' => $user->created_at->toDateTimeString()
                                ]
                            )
                            ->merge(
                                $technicians->map(
                                    fn(Technician $technician) => [
                                        'id' => $technician->id,
                                        'first_name' => $technician->first_name,
                                        'last_name' => $technician->last_name,
                                        'email' => $technician->email,
                                        'phone' => $technician->phone,
                                        'type' => UserMorphEnum::TECHNICIAN,
                                        'created_at' => $technician->created_at->toDateTimeString()
                                    ]
                                )
                            )
                            ->toArray()
                        ]
                    ]
            ]);
    }
}
