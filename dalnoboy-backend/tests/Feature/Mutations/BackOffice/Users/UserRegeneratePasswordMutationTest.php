<?php

namespace Tests\Feature\Mutations\BackOffice\Users;

use App\GraphQL\Mutations\BackOffice\Users\UserRegeneratePasswordMutation;
use App\Models\Users\User;
use App\Notifications\Users\ChangePasswordNotification;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class UserRegeneratePasswordMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginAsAdminWithRole();

        Notification::fake();
    }

    public function test_regenerate_password(): void
    {
        $user = User::factory()
            ->inspector()
            ->create();

        $oldPassword = $user->password;

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(UserRegeneratePasswordMutation::NAME)
                ->args(
                    [
                        'id' => $user->id
                    ]
                )
                ->make()
        )
            ->assertJson(
                [
                    'data' => [
                        UserRegeneratePasswordMutation::NAME => true
                    ]
                ]
            );

        Notification::assertSentTo($user, ChangePasswordNotification::class);

        $this->assertNotEquals($oldPassword, $user->refresh()->password);
    }
}
