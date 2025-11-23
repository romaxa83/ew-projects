<?php

namespace Tests\Feature\Mailbox\Gmail;

use App\Models\Division;
use App\Models\Mailbox\Gmail\Account;
use App\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Gmail\AccountBuilder;
use Tests\Builders\Divisions\DivisionBuilder;
use Tests\TestCase;

class AccountsTest extends TestCase
{
    use DatabaseTransactions;

    protected AccountBuilder $accountBuilder;
    protected DivisionBuilder $divisionBuilder;

    public function setUp(): void
    {
        $this->accountBuilder = resolve(AccountBuilder::class);
        $this->divisionBuilder = resolve(DivisionBuilder::class);

        parent::setUp();
    }

    /** @test */
    public function it_returns_accounts_for_admin_user()
    {
        // Login as admin user
        $user = $this->loginUser(); // true for admin

        /** @var Division $division */
        $division = $this->divisionBuilder->misc([
            "tz" => "America/Chicago"
        ])->create();
        $this->session(['division' => $division->toArray()]);

        /** @var Account $account */
        $account = $this->accountBuilder
            ->division($division)
            ->setData([
                'user_id' => $user->id,
                'active' => 1,
                'is_archived' => 0,
                'miscs' => [
                    'email' => 'test@example.com',
                ]
            ])
            ->create();

        $response = $this->postJson(route('mailbox.accounts'));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'records' => [
                    [
                        'id' => $account->id,
                        'active' => 1,
                        'is_archived' => 0,
                        'division_id' => $division->id,
                        'division_title' => $division->title,
                        'email' => 'test@example.com',
                        'is_holder' => true,
                    ]
                ]
            ]);
    }

    /** @test */
    public function it_returns_accounts_for_regular_user()
    {
        // Login as regular user
        $user = $this->loginUser(); // false for regular user

        /** @var Division $division */
        $division = $this->divisionBuilder->misc([
            "tz" => "America/Chicago"
        ])->create();
        $this->session(['division' => $division->toArray()]);

        // Create an account owned by another user
        $otherUser = User::factory()->create();

        /** @var Account $account */
        $account = $this->accountBuilder
            ->division($division)
            ->setData([
                'user_id' => $otherUser->id,
                'active' => 1,
                'is_archived' => 0,
                'miscs' => [
                    'email' => 'other@example.com',
                ]
            ])
            ->create();

        // Add current user to account's users
        $account->users()->attach($user->id);

        $response = $this->postJson(route('mailbox.accounts'));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'records' => [
                    [
                        'id' => $account->id,
                        'active' => 1,
                        'is_archived' => 0,
                        'division_id' => $division->id,
                        'division_title' => $division->title,
                        'email' => 'other@example.com',
                        'is_holder' => false,
                    ]
                ]
            ]);
    }

    /** @test */
    public function it_returns_empty_records_when_no_accounts()
    {
        // Login as regular user
        $this->loginUser();

        $division = $this->divisionBuilder->misc([
            "tz" => "America/Chicago"
        ])->create();
        $this->session(['division' => $division->toArray()]);

        $response = $this->postJson(route('mailbox.accounts'));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'records' => []
            ])
            ->assertJsonCount(0, 'records');
    }

    /** @test */
    public function it_sorts_accounts_with_user_email_first()
    {
        $division = $this->divisionBuilder->misc([
            "tz" => "America/Chicago"
        ])->create();
        $this->session(['division' => $division->toArray()]);

        // Login as admin user
        $user = $this->loginUser();
        $user->email = 'user@example.com';
        $user->save();

        /** @var Division $division */
        $division = $this->divisionBuilder->create();

        // Create an account with different email
        /** @var Account $account1 */
        $account1 = $this->accountBuilder
            ->division($division)
            ->setData([
                'user_id' => $user->id,
                'active' => 1,
                'is_archived' => 0,
                'miscs' => [
                    'email' => 'other@example.com',
                ]
            ])
            ->create();

        // Create an account with user's email
        /** @var Account $account2 */
        $account2 = $this->accountBuilder
            ->division($division)
            ->setData([
                'user_id' => $user->id,
                'active' => 1,
                'is_archived' => 0,
                'miscs' => [
                    'email' => 'user@example.com',
                ]
            ])
            ->create();

        $response = $this->postJson(route('mailbox.accounts'));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'records' => [
                    [
                        'id' => $account2->id,
                        'email' => 'user@example.com',
                    ],
                    [
                        'id' => $account1->id,
                        'email' => 'other@example.com',
                    ]
                ]
            ]);
    }
}
