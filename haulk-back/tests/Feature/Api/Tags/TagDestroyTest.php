<?php

namespace Api\Tags;

use App\Models\Orders\Order;
use App\Models\Tags\Tag;
use App\Models\Users\User;
use App\Models\Vehicles\Truck;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\TestCase;

class TagDestroyTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_not_delete_for_unauthorized_users()
    {
        $tag = Tag::factory()->create();

        $this->deleteJson(route('tags.destroy', $tag))->assertUnauthorized();
    }

    public function test_it_not_delete_for_not_permitted_users()
    {
        $tag = Tag::factory()->create();

        $this->loginAsCarrierDispatcher();

        $this->deleteJson(route('tags.destroy', $tag))
            ->assertForbidden();
    }

    public function test_it_delete_by_super_admin()
    {
        $tag = Tag::factory()->create();

        $this->assertDatabaseHas(Tag::TABLE_NAME, $tag->getAttributes());

        $this->loginAsCarrierSuperAdmin();
        $this->deleteJson(route('tags.destroy', $tag))
            ->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseMissing(Tag::TABLE_NAME, $tag->getAttributes());
    }

    public function test_it_delete_by_admin()
    {
        $tag = Tag::factory()->create();

        $this->assertDatabaseHas(Tag::TABLE_NAME, $tag->getAttributes());

        $this->loginAsCarrierAdmin();
        $this->deleteJson(route('tags.destroy', $tag))
            ->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseMissing(Tag::TABLE_NAME, $tag->getAttributes());
    }

    public function test_it_delete_with_related_entities()
    {
        $this->loginAsCarrierSuperAdmin();

        $tag = Tag::factory()->create(['type' => Tag::TYPE_ORDER]);
        $order = Order::factory()->create();
        $order->tags()->sync([$tag->id]);

        $this->deleteJson(route('tags.destroy', $tag))
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertDatabaseHas(Tag::TABLE_NAME, $tag->getAttributes());

        $tag = Tag::factory()->create(['type' => Tag::TYPE_VEHICLE_OWNER]);
        $user = User::factory()->create();
        $user->tags()->sync([$tag->id]);

        $this->deleteJson(route('tags.destroy', $tag))
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertDatabaseHas(Tag::TABLE_NAME, $tag->getAttributes());

        $tag = Tag::factory()->create(['type' => Tag::TYPE_TRUCKS_AND_TRAILER]);
        $truck = factory(Truck::class)->create();
        $truck->tags()->sync([$tag->id]);

        $this->deleteJson(route('tags.destroy', $tag))
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertDatabaseHas(Tag::TABLE_NAME, $tag->getAttributes());
    }
}
