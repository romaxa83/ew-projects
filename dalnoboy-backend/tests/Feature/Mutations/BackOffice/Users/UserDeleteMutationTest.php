<?php

namespace Tests\Feature\Mutations\BackOffice\Users;

use App\Enums\Utilities\MorphModelNameEnum;
use App\GraphQL\Mutations\BackOffice\Users\UserDeleteMutation;
use App\Models\Phones\Phone;
use App\Models\Users\User;
use App\Models\Users\UserBranch;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class UserDeleteMutationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_delete_user(): void
    {
        $this->loginAsAdminWithRole();

        $user = User::factory()
            ->inspector()
            ->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(UserDeleteMutation::NAME)
                ->args(
                    [
                        'id' => $user->id
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        UserDeleteMutation::NAME => true
                    ]
                ]
            );

        $this->assertDatabaseMissing(
            User::class,
            [
                'id' => $user->id
            ]
        );

        $this->assertDatabaseMissing(
            UserBranch::class,
            [
                'user_id' => $user->id
            ]
        );

        $this->assertDatabaseMissing(
            Phone::class,
            [
                'owner_id' => $user->id,
                'owner_type' => MorphModelNameEnum::user()->key
            ]
        );
    }
}
