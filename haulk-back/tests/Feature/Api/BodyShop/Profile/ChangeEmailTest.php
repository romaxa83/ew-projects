<?php

namespace Tests\Feature\Api\BodyShop\Profile;

use App\Models\Users\ChangeEmail;
use App\Models\Users\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

class ChangeEmailTest extends TestCase
{
    use DatabaseTransactions;

    public function test_change_email_request(): void
    {
        $user = $this->loginAsBodyShopSuperAdmin();

        $attributes = [
            'new_email' => 'test@test.com',
        ];

        $this->postJson(route('body-shop.change-email.store'), $attributes)
            ->assertCreated();

        $this->assertDatabaseHas('change_emails', [
            'new_email' => $attributes['new_email'],
            'old_email' => $user->email,
            'user_id' => $user->id,
        ]);
    }

    public function test_change_email_delete_request(): void
    {
        $user = $this->loginAsBodyShopSuperAdmin();

        $attributes = [
            'new_email' => 'test@test.com',
        ];

        $response = $this->postJson(route('body-shop.change-email.store'), $attributes)
            ->assertCreated();

        $data = $response['data']['id'];

        $this->assertDatabaseHas('change_emails', [
            'new_email' => $attributes['new_email'],
            'old_email' => $user->email,
            'user_id' => $user->id,
        ]);

        $this->deleteJson(route('body-shop.change-email.destroy', $data), $attributes)
            ->assertNoContent();

        $this->assertDatabaseMissing('change_emails', [
            'new_email' => $attributes['new_email'],
            'old_email' => $user->email,
            'user_id' => $user->id,
        ]);
    }

    public function test_change_email_if_requested(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $attributes = [
            'new_email' => 'test@test.com',
        ];

        $this->postJson(route('body-shop.change-email.store'), $attributes)
            ->assertCreated();

        $this->getJson(route('body-shop.change-email.if-requested'), $attributes)
            ->assertOk();
    }

    public function test_change_email_confirm(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole(User::BSSUPERADMIN_ROLE);

        $newEmail = 'test@test.com';
        $confirmToken = Str::random(60);
        $declineToken = Str::random(60);

        $changeEmail = new ChangeEmail();
        $changeEmail->new_email =$newEmail;
        $changeEmail->old_email = $user->email;
        $changeEmail->user_id = $user->id;
        $changeEmail->confirm_token = hash('sha256', $confirmToken);
        $changeEmail->decline_token = hash('sha256', $declineToken);
        $changeEmail->save();
        $changeEmail->refresh();

        $this->postJson(route('body-shop.change-email.confirm-email'), [
            'email' => $newEmail,
            'token' => $confirmToken,
        ])
            ->assertOk();

        $this->assertDatabaseMissing('change_emails', [
            'new_email' => $newEmail,
            'old_email' => $user->email,
            'user_id' => $user->id,
        ]);

        $user->refresh();
        $this->assertEquals($newEmail, $user->email);
    }

    public function test_change_email_cancel(): void
    {
        /** @var User $user */
        $user = User::factory()->create();
        $user->assignRole(User::BSSUPERADMIN_ROLE);
        $oldEmail = $user->email;

        $newEmail = 'test@test.com';
        $confirmToken = Str::random(60);
        $declineToken = Str::random(60);

        $changeEmail = new ChangeEmail();
        $changeEmail->new_email =$newEmail;
        $changeEmail->old_email = $user->email;
        $changeEmail->user_id = $user->id;
        $changeEmail->confirm_token = hash('sha256', $confirmToken);
        $changeEmail->decline_token = hash('sha256', $declineToken);
        $changeEmail->save();
        $changeEmail->refresh();

        $this->postJson(route('body-shop.change-email.cancel-request'), [
            'email' => $newEmail,
            'token' => $declineToken,
        ])
            ->assertNoContent();

        $this->assertDatabaseMissing('change_emails', [
            'new_email' => $newEmail,
            'old_email' => $user->email,
            'user_id' => $user->id,
        ]);

        $user->refresh();
        $this->assertEquals($oldEmail, $user->email);
    }
}
