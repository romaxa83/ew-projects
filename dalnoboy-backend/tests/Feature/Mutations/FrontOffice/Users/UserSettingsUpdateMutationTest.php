<?php

namespace Tests\Feature\Mutations\FrontOffice\Users;

use App\Enums\Users\AuthorizationExpirationPeriodEnum;
use App\GraphQL\Mutations\FrontOffice\Users\UserSettingsUpdateMutation;
use App\Models\Users\User;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class UserSettingsUpdateMutationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_update_user_settings(): void
    {
        $user = User::factory()
            ->inspector()
            ->create();

        $this->loginAsUserWithRole($user);

        $this->postGraphQL(
            GraphQLQuery::mutation(UserSettingsUpdateMutation::NAME)
                ->args(
                    [
                        'settings' => [
                            'authorization_expiration_period' => AuthorizationExpirationPeriodEnum::EVERYDAY(),
                        ]
                    ]
                )
                ->select(
                    [
                        'id',
                        'authorization_expiration_period',
                    ]
                )
                ->make()
        )
            ->assertJson(
                [
                    'data' => [
                        UserSettingsUpdateMutation::NAME => [
                            'id' => $user->id,
                            'authorization_expiration_period' =>  AuthorizationExpirationPeriodEnum::EVERYDAY(),
                        ]
                    ]
                ]
            );

        $this->assertDatabaseHas(
            User::class,
            [
                'id' => $user->id,
                'authorization_expiration_period' => AuthorizationExpirationPeriodEnum::EVERYDAY,
            ]
        );
    }
}
