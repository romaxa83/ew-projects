<?php

namespace Api\Users\OwnerDriver;

use App\Broadcasting\Events\User\DeleteUserBroadcast;
use App\Models\Users\User;
use App\Models\Vehicles\Truck;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class OwnerDriverDestroyTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_delete_user_with_vehicles_of_owner(): void
    {
        /** @var User $user */
        $user = User::factory()->create(['status' => User::STATUS_INACTIVE]);
        $user->syncRoles(User::OWNER_DRIVER_ROLE);

        $this->assertDatabaseHas(User::TABLE_NAME, $user->getAttributes());
        factory(Truck::class)->create(['owner_id' => $user->id]);

        $this->loginAsCarrierSuperAdmin();
        $this->deleteJson(route('users.destroy', $user))
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertDatabaseHas(User::TABLE_NAME, $user->getAttributes());
    }

    public function test_it_delete_not_active_user()
    {
        /** @var User $user */
        $user = User::factory()->create(['status' => User::STATUS_INACTIVE]);
        $user->syncRoles(User::OWNER_DRIVER_ROLE);

        $this->assertDatabaseHas(User::TABLE_NAME, $user->getAttributes());

        $this->loginAsCarrierSuperAdmin();

        Event::fake([
            DeleteUserBroadcast::class
        ]);

        $this->deleteJson(route('users.destroy', $user))
            ->assertStatus(Response::HTTP_NO_CONTENT);

        Event::assertDispatched(DeleteUserBroadcast::class);

        $this->assertDatabaseMissing(User::TABLE_NAME, $user->getAttributes());
    }
}
