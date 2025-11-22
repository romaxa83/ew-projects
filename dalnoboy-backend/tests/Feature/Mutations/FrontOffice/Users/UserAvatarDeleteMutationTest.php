<?php

namespace Tests\Feature\Mutations\FrontOffice\Users;

use App\GraphQL\Mutations\FrontOffice\Users\UserAvatarDeleteMutation;
use App\Models\Media\Media;
use App\Models\Users\User;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class UserAvatarDeleteMutationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_delete_user_avatar(): void
    {
        $user = User::factory()
            ->inspector()
            ->create();

        Media::factory()
            ->imagePng()
            ->toModel($user)
            ->toCollection(User::MC_AVATAR)
            ->create();

        $this->loginAsUserWithRole($user);

        $this->assertNotEmpty($user->avatar);

        $this->postGraphQl(
            GraphQLQuery::mutation(UserAvatarDeleteMutation::NAME)
                ->select(
                    [
                        'id',
                        'avatar' => [
                            'id',
                            'url',
                        ],
                    ]
                )
                ->make()
        )
            ->assertOk();

        $user->refresh();
        $this->assertEmpty($user->avatar);
    }
}
