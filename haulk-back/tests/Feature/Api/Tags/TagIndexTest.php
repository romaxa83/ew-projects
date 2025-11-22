<?php

namespace Api\Tags;

use App\Models\Orders\Order;
use App\Models\Tags\Tag;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class TagIndexTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    public function test_it_forbidden_for_not_authorized_users(): void
    {
        $this->getJson(route('tags.index'))
            ->assertUnauthorized();
    }

    public function test_it_forbidden_for_permitted_users(): void
    {
        $this->loginAsCarrierDriver();

        $this->getJson(route('tags.index'))
            ->assertForbidden();
    }

    public function test_it_show_all_for_super_admin(): void
    {
        $this->loginAsCarrierSuperAdmin();

        $this->getJson(route('tags.index'))
            ->assertOk();
    }

    public function test_it_show_all_for_admin(): void
    {
        $this->loginAsCarrierAdmin();

        $this->getJson(route('tags.index'))
            ->assertOk();
    }

    public function test_it_show_all_for_dispatcher(): void
    {
        $this->loginAsCarrierDispatcher();

        $this->getJson(route('tags.index'))
            ->assertOk();
    }

    public function test_it_show_all_for_accountant(): void
    {
        $this->loginAsCarrierAccountant();

        $this->getJson(route('tags.index'))
            ->assertOk();
    }

    public function test_it_show_related_vehicles_fields(): void
    {
        $this->loginAsCarrierSuperAdmin();

        $tag = Tag::factory()->create(['type' => Tag::TYPE_TRUCKS_AND_TRAILER]);

        $response = $this->getJson(route('tags.index'))
            ->assertOk();

        $this->assertFalse($response['data'][Tag::TYPE_TRUCKS_AND_TRAILER][0]['hasRelatedTrucks']);
        $this->assertFalse($response['data'][Tag::TYPE_TRUCKS_AND_TRAILER][0]['hasRelatedTrailers']);
        $this->assertFalse($response['data'][Tag::TYPE_TRUCKS_AND_TRAILER][0]['hasRelatedEntities']);

        $truck = factory(Truck::class)->create();
        $truck->tags()->sync([$tag->id]);

        $response = $this->getJson(route('tags.index'))
            ->assertOk();

        $this->assertTrue($response['data'][Tag::TYPE_TRUCKS_AND_TRAILER][0]['hasRelatedTrucks']);
        $this->assertFalse($response['data'][Tag::TYPE_TRUCKS_AND_TRAILER][0]['hasRelatedTrailers']);
        $this->assertTrue($response['data'][Tag::TYPE_TRUCKS_AND_TRAILER][0]['hasRelatedEntities']);

        $trailer = factory(Trailer::class)->create();
        $trailer->tags()->sync([$tag->id]);

        $response = $this->getJson(route('tags.index'))
            ->assertOk();

        $this->assertTrue($response['data'][Tag::TYPE_TRUCKS_AND_TRAILER][0]['hasRelatedTrucks']);
        $this->assertTrue($response['data'][Tag::TYPE_TRUCKS_AND_TRAILER][0]['hasRelatedTrailers']);
        $this->assertTrue($response['data'][Tag::TYPE_TRUCKS_AND_TRAILER][0]['hasRelatedEntities']);
    }

    public function test_it_show_related_owners_fields(): void
    {
        $this->loginAsCarrierSuperAdmin();

        $tag = Tag::factory()->create(['type' => Tag::TYPE_VEHICLE_OWNER]);

        $response = $this->getJson(route('tags.index'))
            ->assertOk();

        $this->assertFalse($response['data'][Tag::TYPE_VEHICLE_OWNER][0]['hasRelatedEntities']);

        $owner = $this->ownerFactory();
        $owner->tags()->sync([$tag->id]);

        $response = $this->getJson(route('tags.index'))
            ->assertOk();

        $this->assertTrue($response['data'][Tag::TYPE_VEHICLE_OWNER][0]['hasRelatedEntities']);
    }

    public function test_it_show_related_orders_fields(): void
    {
        $this->loginAsCarrierSuperAdmin();

        $tag = Tag::factory()->create(['type' => Tag::TYPE_ORDER]);

        $response = $this->getJson(route('tags.index'))
            ->assertOk();

        $this->assertFalse($response['data'][Tag::TYPE_ORDER][0]['hasRelatedEntities']);

        $order = Order::factory()->create();
        $order->tags()->sync([$tag->id]);

        $response = $this->getJson(route('tags.index'))
            ->assertOk();

        $this->assertTrue($response['data'][Tag::TYPE_ORDER][0]['hasRelatedEntities']);
    }
}
