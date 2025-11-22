<?php

namespace Tests\Feature\Api\BodyShop\Users;

use App\Models\Users\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class UserChangeStatusTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    public function test_it_forbidden_for_not_authorized_users(): void
    {
        $user = $this->bsAdminFactory();

        $this->putJson(route('body-shop.users.change-status', $user))
            ->assertUnauthorized();
    }

    public function test_it_forbidden_for_permitted_users(): void
    {
        $this->loginAsBodyShopMechanic();

        $user = $this->bsAdminFactory();

        $this->putJson(route('body-shop.users.change-status', $user))
            ->assertForbidden();
    }

    public function test_it_not_found_for_company_user(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $user = $this->dispatcherFactory();

        $this->putJson(route('body-shop.users.change-status', $user))
            ->assertNotFound();
    }

    public function test_it_success_to_active_bs_admin(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $user = $this->bsAdminFactory(['status' => User::STATUS_INACTIVE]);

        $this->putJson(route('body-shop.users.change-status', $user))
            ->assertOk();

        $user->refresh();
        $this->assertEquals(User::STATUS_ACTIVE, $user->status);
    }

    public function test_it_success_to_inactive_bs_admin(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $user = $this->bsAdminFactory(['status' => User::STATUS_ACTIVE]);

        $this->putJson(route('body-shop.users.change-status', $user))
            ->assertOk();

        $user->refresh();
        $this->assertEquals(User::STATUS_INACTIVE, $user->status);
    }

    public function test_it_error_from_pending(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $user = $this->bsAdminFactory(['status' => User::STATUS_PENDING]);

        $this->putJson(route('body-shop.users.change-status', $user))
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson([
                'errors' => [
                    [
                        'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                        'title' => trans('Can\'t change status for Pending'),
                    ]
                ],
            ]);

        $user->refresh();
        $this->assertEquals(User::STATUS_PENDING, $user->status);
    }
}
