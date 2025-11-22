<?php

namespace Tests\Feature\Mutations\BackOffice\Admins;

use App\GraphQL\Mutations\BackOffice\Admins\AdminRecoverPasswordMutation;
use App\Models\Admins\Admin;
use App\Notifications\Admins\RecoverPasswordNotification;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AdminRecoverPasswordMutationTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        Notification::fake();
    }

    public function test_recover_password(): void
    {
        $admin = Admin::factory()->create();
        $this->assertEmpty($admin->recover_password_expires_at);

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(AdminRecoverPasswordMutation::NAME)
                ->args(
                    [
                        'email' => $admin->email,
                    ]
                )
                ->select()
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        AdminRecoverPasswordMutation::NAME => true,
                    ]
                ]
            );

        $admin->refresh();
        $this->assertNotEmpty($admin->recover_password_expires_at);

        Notification::assertSentTo(
            $admin,
            RecoverPasswordNotification::class
        );
    }
}
