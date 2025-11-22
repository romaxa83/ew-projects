<?php

namespace Tests\Feature\Api\BodyShop\Users;

use App\Broadcasting\Events\User\DeleteUserBroadcast;
use App\Models\BodyShop\Orders\Order;
use App\Models\Users\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class UserDestroyTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    public function test_it_not_delete_user_for_unauthorized_users(): void
    {
        $user = $this->bsAdminFactory();

        $this->deleteJson(route('body-shop.users.destroy', $user))
            ->assertUnauthorized();
    }

    public function test_it_not_delete_user_for_not_permitted_users(): void
    {
        $user = $this->bsAdminFactory();

        $this->loginAsBodyShopMechanic();

        $this->deleteJson(route('body-shop.users.destroy', $user))
            ->assertForbidden();
    }

    public function test_it_not_delete_user_from_company(): void
    {
        $user = $this->dispatcherFactory();

        $this->loginAsBodyShopAdmin();

        $this->deleteJson(route('body-shop.users.destroy', $user))
            ->assertNotFound();
    }


    public function test_it_delete_active_user_role(): void
    {
        $user = $this->bsAdminFactory();

        $this->assertDatabaseHas(User::TABLE_NAME, $user->getAttributes());

        $this->loginAsBodyShopSuperAdmin();
        $this->deleteJson(route('body-shop.users.destroy', $user))
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertDatabaseHas(User::TABLE_NAME, $user->getAttributes());
    }

    public function test_s_admin_can_delete_not_active_admin_and_mechanic(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $user = $this->bsAdminFactory(['status' => User::STATUS_INACTIVE]);

        $this->assertDatabaseHas(User::TABLE_NAME, $user->getAttributes());

        Event::fake([
            DeleteUserBroadcast::class
        ]);

        $this->deleteJson(route('body-shop.users.destroy', $user))
            ->assertStatus(Response::HTTP_NO_CONTENT);

        Event::assertDispatched(DeleteUserBroadcast::class);

        $this->assertDatabaseMissing(User::TABLE_NAME, $user->getAttributes());

        $user = $mechanic = $this->bsMechanicFactory(['status' => User::STATUS_INACTIVE]);

        $this->assertDatabaseHas(User::TABLE_NAME, $user->getAttributes());

        $this->deleteJson(route('body-shop.users.destroy', $user))
            ->assertStatus(Response::HTTP_NO_CONTENT);

        Event::assertDispatched(DeleteUserBroadcast::class);

        $this->assertDatabaseMissing(User::TABLE_NAME, $user->getAttributes());
    }

    public function test_s_admin_cant_delete_s_admin(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $user = $this->bsSuperAdminFactory(['status' => User::STATUS_INACTIVE]);

        $this->assertDatabaseHas(User::TABLE_NAME, $user->getAttributes());

        $this->deleteJson(route('body-shop.users.destroy', $user))
            ->assertForbidden();

        $this->assertDatabaseHas(User::TABLE_NAME, $user->getAttributes());
    }

    public function test_admin_cant_delete_s_admin_and_admin(): void
    {
        $this->loginAsBodyShopAdmin();

        $user = $this->bsSuperAdminFactory(['status' => User::STATUS_INACTIVE]);

        $this->assertDatabaseHas(User::TABLE_NAME, $user->getAttributes());

        $this->deleteJson(route('body-shop.users.destroy', $user))
            ->assertForbidden();

        $this->assertDatabaseHas(User::TABLE_NAME, $user->getAttributes());


        $user = $this->bsAdminFactory(['status' => User::STATUS_INACTIVE]);

        $this->assertDatabaseHas(User::TABLE_NAME, $user->getAttributes());

        $this->deleteJson(route('body-shop.users.destroy', $user))
            ->assertForbidden();

        $this->assertDatabaseHas(User::TABLE_NAME, $user->getAttributes());
    }

    public function test_admin_can_delete_not_active_mechanic(): void
    {
        $this->loginAsBodyShopAdmin();

        $user = $this->bsMechanicFactory(['status' => User::STATUS_INACTIVE]);

        $this->assertDatabaseHas(User::TABLE_NAME, $user->getAttributes());

        Event::fake([
            DeleteUserBroadcast::class
        ]);

        $this->deleteJson(route('body-shop.users.destroy', $user))
            ->assertStatus(Response::HTTP_NO_CONTENT);

        Event::assertDispatched(DeleteUserBroadcast::class);

        $this->assertDatabaseMissing(User::TABLE_NAME, $user->getAttributes());
    }

    public function test_it_delete_with_new_order(): void
    {
        $mechanic = $this->bsMechanicFactory(['status' => User::STATUS_INACTIVE]);
        factory(Order::class)->create([
            'mechanic_id' => $mechanic->id,
            'status' => Order::STATUS_NEW,
            'status_changed_at' => now(),
        ]);

        $this->assertDatabaseHas(User::TABLE_NAME, $mechanic->getAttributes());

        $this->loginAsBodyShopAdmin();
        $this->deleteJson(route('body-shop.users.destroy', $mechanic))
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertDatabaseHas(User::TABLE_NAME, $mechanic->getAttributes());
    }


    public function test_it_delete_with_in_process_order(): void
    {
        $mechanic = $this->bsMechanicFactory(['status' => User::STATUS_INACTIVE]);
        factory(Order::class)->create([
            'mechanic_id' => $mechanic->id,
            'status' => Order::STATUS_IN_PROCESS,
            'status_changed_at' => now(),
        ]);

        $this->assertDatabaseHas(User::TABLE_NAME, $mechanic->getAttributes());

        $this->loginAsBodyShopAdmin();
        $this->deleteJson(route('body-shop.users.destroy', $mechanic))
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertDatabaseHas(User::TABLE_NAME, $mechanic->getAttributes());
    }


    public function test_it_delete_with_finished_order_less_than_24_hours(): void
    {
        $mechanic = $this->bsMechanicFactory(['status' => User::STATUS_INACTIVE]);

        factory(Order::class)->create([
            'mechanic_id' => $mechanic->id,
            'status' => Order::STATUS_FINISHED,
            'status_changed_at' => now(),
        ]);

        $this->assertDatabaseHas(User::TABLE_NAME, $mechanic->getAttributes());

        $this->loginAsBodyShopAdmin();
        $this->deleteJson(route('body-shop.users.destroy', $mechanic))
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertDatabaseHas(User::TABLE_NAME, $mechanic->getAttributes());
    }

    public function test_it_delete_with_finished_order_more_than_24_hours(): void
    {
        $mechanic = $this->bsMechanicFactory(['status' => User::STATUS_INACTIVE]);
        factory(Order::class)->create([
            'mechanic_id' => $mechanic->id,
            'status' => Order::STATUS_FINISHED,
            'status_changed_at' => now()->addHours(-25),
        ]);

        $this->assertDatabaseHas(User::TABLE_NAME, $mechanic->getAttributes());

        $this->loginAsBodyShopAdmin();
        $this->deleteJson(route('body-shop.users.destroy', $mechanic))
            ->assertStatus(Response::HTTP_NO_CONTENT);

        $mechanic->refresh();
        $this->assertDatabaseHas(User::TABLE_NAME, $mechanic->getAttributes());
        $this->assertNotEmpty($mechanic->deleted_at);
    }

    public function test_it_delete_with_deleted_order(): void
    {
        $mechanic = $this->bsMechanicFactory(['status' => User::STATUS_INACTIVE]);
        $order = factory(Order::class)->create([
            'mechanic_id' => $mechanic->id,
            'status' => Order::STATUS_FINISHED,
            'status_changed_at' => now(),
        ]);
        $order->delete();

        $this->assertDatabaseHas(User::TABLE_NAME, $mechanic->getAttributes());

        $this->loginAsBodyShopAdmin();
        $this->deleteJson(route('body-shop.users.destroy', $mechanic))
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertDatabaseHas(User::TABLE_NAME, $mechanic->getAttributes());
    }
}
