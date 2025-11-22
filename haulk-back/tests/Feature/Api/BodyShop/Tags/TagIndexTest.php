<?php

namespace Api\BodyShop\Tags;

use App\Models\BodyShop\VehicleOwners\VehicleOwner;
use App\Models\Tags\Tag;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TagIndexTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_forbidden_for_not_authorized_users(): void
    {
        $this->getJson(route('body-shop.tags.index'))
            ->assertUnauthorized();
    }

    public function test_it_forbidden_for_permitted_users(): void
    {
        $this->loginAsCarrierDriver();

        $this->getJson(route('body-shop.tags.index'))
            ->assertForbidden();
    }

    public function test_it_show_all_for_bs_super_admin(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $this->getJson(route('body-shop.tags.index'))
            ->assertOk();
    }

    public function test_it_show_all_for_bs_admin(): void
    {
        $this->loginAsBodyShopAdmin();

        $this->getJson(route('body-shop.tags.index'))
            ->assertOk();
    }

    public function test_it_show_related_vehicles_fields(): void
    {
        $this->loginAsBodyShopAdmin();

        $tag = Tag::factory()->create(['type' => Tag::TYPE_TRUCKS_AND_TRAILER, 'carrier_id' => null]);

        $response = $this->getJson(route('body-shop.tags.index'))
            ->assertOk();

        $this->assertFalse($response['data'][Tag::TYPE_TRUCKS_AND_TRAILER][0]['hasRelatedTrucks']);
        $this->assertFalse($response['data'][Tag::TYPE_TRUCKS_AND_TRAILER][0]['hasRelatedTrailers']);
        $this->assertFalse($response['data'][Tag::TYPE_TRUCKS_AND_TRAILER][0]['hasRelatedEntities']);

        $truck = factory(Truck::class)->create(['carrier_id' => null]);
        $truck->tags()->sync([$tag->id]);

        $response = $this->getJson(route('body-shop.tags.index'))
            ->assertOk();

        $this->assertTrue($response['data'][Tag::TYPE_TRUCKS_AND_TRAILER][0]['hasRelatedTrucks']);
        $this->assertFalse($response['data'][Tag::TYPE_TRUCKS_AND_TRAILER][0]['hasRelatedTrailers']);
        $this->assertTrue($response['data'][Tag::TYPE_TRUCKS_AND_TRAILER][0]['hasRelatedEntities']);

        $trailer = factory(Trailer::class)->create(['carrier_id' => null]);
        $trailer->tags()->sync([$tag->id]);

        $response = $this->getJson(route('body-shop.tags.index'))
            ->assertOk();

        $this->assertTrue($response['data'][Tag::TYPE_TRUCKS_AND_TRAILER][0]['hasRelatedTrucks']);
        $this->assertTrue($response['data'][Tag::TYPE_TRUCKS_AND_TRAILER][0]['hasRelatedTrailers']);
        $this->assertTrue($response['data'][Tag::TYPE_TRUCKS_AND_TRAILER][0]['hasRelatedEntities']);
    }

    public function test_it_show_related_owners_fields(): void
    {
        $this->loginAsBodyShopAdmin();

        $tag = Tag::factory()->create(['type' => Tag::TYPE_VEHICLE_OWNER, 'carrier_id' => null]);

        $response = $this->getJson(route('body-shop.tags.index'))
            ->assertOk();

        $this->assertFalse($response['data'][Tag::TYPE_VEHICLE_OWNER][0]['hasRelatedEntities']);

        $owner = factory(VehicleOwner::class)->create();
        $owner->tags()->sync([$tag->id]);

        $response = $this->getJson(route('body-shop.tags.index'))
            ->assertOk();

        $this->assertTrue($response['data'][Tag::TYPE_VEHICLE_OWNER][0]['hasRelatedEntities']);
    }
}
