<?php

namespace Tests\Feature\Api\BodyShop\Users;

use App\Models\Users\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class UserResendInvitationLinkTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    public function test_it_forbidden_for_not_authorized_users(): void
    {
        $user = $this->bsAdminFactory(['status' => User::STATUS_PENDING]);

        $this->putJson(route('body-shop.users.resendInvitationLink', $user), [])
            ->assertUnauthorized();
    }

    public function test_it_not_found_to_company_user(): void
    {
        $user = $this->dispatcherFactory(['status' => User::STATUS_PENDING]);

        $this->loginAsBodyShopSuperAdmin();

        $this->putJson(route('body-shop.users.resendInvitationLink', $user), [])
            ->assertNotFound();
    }

    public function test_it_success(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $user = $this->bsAdminFactory(['status' => User::STATUS_PENDING]);

        $this->putJson(route('body-shop.users.resendInvitationLink', $user))
            ->assertStatus(Response::HTTP_NO_CONTENT);
    }

    public function test_it_error_for_not_pending_statuses(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $user = $this->bsAdminFactory(['status' => User::STATUS_ACTIVE]);

        $this->putJson(route('body-shop.users.resendInvitationLink', $user))
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $user = $this->bsAdminFactory(['status' => User::STATUS_INACTIVE]);

        $this->putJson(route('body-shop.users.resendInvitationLink', $user))
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
