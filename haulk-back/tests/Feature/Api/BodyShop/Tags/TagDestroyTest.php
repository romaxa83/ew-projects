<?php

namespace Api\BodyShop\Tags;

use App\Models\BodyShop\VehicleOwners\VehicleOwner;
use App\Models\Tags\Tag;
use App\Models\Vehicles\Trailer;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\TestCase;

class TagDestroyTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_not_delete_for_unauthorized_users()
    {
        $tag = Tag::factory()->create();

        $this->deleteJson(route('body-shop.tags.destroy', $tag))->assertUnauthorized();
    }

    public function test_it_not_delete_for_not_permitted_users()
    {
        $tag = Tag::factory()->create();

        $this->loginAsCarrierDispatcher();

        $this->deleteJson(route('body-shop.tags.destroy', $tag))
            ->assertForbidden();
    }

    public function test_it_delete_by_bs_super_admin()
    {
        $tag = Tag::factory()->create(['carrier_id' => null]);

        $this->assertDatabaseHas(Tag::TABLE_NAME, $tag->getAttributes());

        $this->loginAsBodyShopSuperAdmin();
        $this->deleteJson(route('body-shop.tags.destroy', $tag))
            ->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseMissing(Tag::TABLE_NAME, $tag->getAttributes());
    }

    public function test_it_delete_by_bs_admin()
    {
        $tag = Tag::factory()->create(['carrier_id' => null]);

        $this->assertDatabaseHas(Tag::TABLE_NAME, $tag->getAttributes());

        $this->loginAsBodyShopAdmin();
        $this->deleteJson(route('body-shop.tags.destroy', $tag))
            ->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseMissing(Tag::TABLE_NAME, $tag->getAttributes());
    }

    public function test_it_delete_with_related_entities()
    {
        $this->loginAsBodyShopSuperAdmin();

        $tag = Tag::factory()->create(['carrier_id' => null, 'type' => Tag::TYPE_VEHICLE_OWNER]);
        $vehicleOwner = factory(VehicleOwner::class)->create();
        $vehicleOwner->tags()->sync([$tag->id]);

        $this->deleteJson(route('body-shop.tags.destroy', $tag))
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertDatabaseHas(Tag::TABLE_NAME, $tag->getAttributes());

        $tag = Tag::factory()->create(['carrier_id' => null, 'type' => Tag::TYPE_TRUCKS_AND_TRAILER]);
        $trailer = factory(Trailer::class)->create();
        $trailer->tags()->sync([$tag->id]);

        $this->deleteJson(route('body-shop.tags.destroy', $tag))
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertDatabaseHas(Tag::TABLE_NAME, $tag->getAttributes());
    }
}
