<?php

namespace Tests\Feature\Mutations\BackOffice\Admins;

use App\GraphQL\Mutations\BackOffice\Admins\AdminSetPasswordMutation;
use App\Models\Admins\Admin;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AdminSetPasswordMutationTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        Notification::fake();
    }

    public function test_set_password(): void
    {
        $expiresAt = now()->addMinutes(30);
        $admin = Admin::factory()->create(['recover_password_expires_at' => $expiresAt]);
        $token = Crypt::encryptString(arrayToJson([
            'id' => $admin->id,
            'time' => $expiresAt->getTimestamp(),
        ]));

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(AdminSetPasswordMutation::NAME)
                ->args(
                    [
                        'password' => '12345678QW',
                        'password_confirmation' => '12345678QW',
                        'token' => $token,
                    ]
                )
                ->select()
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        AdminSetPasswordMutation::NAME => true,
                    ]
                ]
            );

        $admin->refresh();
        $this->assertEmpty($admin->recover_password_expires_at);
    }

    public function test_set_password_exception(): void
    {
        $expiresAt = now()->addMinutes(-30);
        $admin = Admin::factory()->create(['recover_password_expires_at' => $expiresAt]);
        $token = Crypt::encryptString(arrayToJson([
            'id' => $admin->id,
            'time' => $expiresAt->getTimestamp(),
        ]));

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(AdminSetPasswordMutation::NAME)
                ->args(
                    [
                        'password' => '12345678QW',
                        'password_confirmation' => '12345678QW',
                        'token' => $token,
                    ]
                )
                ->select()
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'errors' => [
                        [
                            'message' => trans('exceptions.expired-link'),
                        ]
                    ]
                ]
            );
    }
}
