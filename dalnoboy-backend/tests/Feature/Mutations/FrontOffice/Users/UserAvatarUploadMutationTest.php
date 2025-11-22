<?php

namespace Tests\Feature\Mutations\FrontOffice\Users;

use App\GraphQL\Mutations\FrontOffice\Users\UserAvatarUploadMutation;
use App\Models\Users\User;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class UserAvatarUploadMutationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_user_avatar_upload(): void
    {
        $user = User::factory()
            ->inspector()
            ->create();

        $this->loginAsUserWithRole($user);

        $this->assertEmpty($user->avatar);

        $this->postGraphQlUpload(
            GraphQLQuery::upload(UserAvatarUploadMutation::NAME)
                ->args(
                    [
                        'file' => UploadedFile::fake()->create('new-logo.jpg', 500, 'image/jpeg'),
                    ]
                )
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
        $this->assertNotEmpty($user->avatar);
    }
}
