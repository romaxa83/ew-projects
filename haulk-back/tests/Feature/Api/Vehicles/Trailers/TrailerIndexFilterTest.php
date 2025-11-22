<?php

namespace Tests\Feature\Api\Vehicles\Trailers;

use App\Models\Tags\Tag;
use App\Models\Users\User;
use App\Models\Vehicles\Trailer;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class TrailerIndexFilterTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    protected string $routeName = 'trailers.index';

    public function test_search(): void
    {
        $this->loginAsCarrierSuperAdmin();

        factory(Trailer::class)->create([
            'vin' => 'TEST123kjhjkh',
            'unit_number' => 'DF12',
            'license_plate' => 'FD1-123',
            'temporary_plate' => 'GGDD-234',
        ]);

        factory(Trailer::class)->create([
            'vin' => 'HFJ123kjhjkh',
            'unit_number' => 'TE123',
            'license_plate' => 'RFD-234',
            'temporary_plate' => 'DDD-234',
        ]);

        factory(Trailer::class)->create([
            'vin' => 'GMGI',
            'unit_number' => 'KG34',
            'license_plate' => 'HFIV-234',
            'temporary_plate' => 'ABVU-234',
        ]);

        $filter = ['q' => 'TEST'];
        $response = $this->getJson(route($this->routeName, $filter))
            ->assertOk();

        $trailers = $response->json('data');
        $this->assertCount(1, $trailers);

        $filter = ['q' => '123'];
        $response = $this->getJson(route($this->routeName, $filter))
            ->assertOk();

        $trailers = $response->json('data');
        $this->assertCount(2, $trailers);

        $filter = ['q' => '234'];
        $response = $this->getJson(route($this->routeName, $filter))
            ->assertOk();

        $trailers = $response->json('data');
        $this->assertCount(3, $trailers);
    }

    public function test_filter_by_driver(): void
    {
        $this->loginAsCarrierSuperAdmin();

        $driver1 = $this->driverFactory();
        factory(Trailer::class)->times(3)->create(['driver_id' => $driver1->id]);

        $driver2 = $this->driverFactory();
        factory(Trailer::class)->times(2)->create(['driver_id' => $driver2->id]);

        $filter = ['driver_id' => $driver2->id];
        $response = $this->getJson(route($this->routeName, $filter))
            ->assertOk();

        $trailers = $response->json('data');
        $this->assertCount(2, $trailers);
    }

    public function test_filter_by_owner(): void
    {
        $this->loginAsCarrierSuperAdmin();

        $owner1 = User::factory()->create();
        factory(Trailer::class)->times(3)->create(['owner_id' => $owner1->id]);

        $owner2 = User::factory()->create();
        factory(Trailer::class)->times(2)->create(['owner_id' => $owner2->id]);

        $filter = ['owner_id' => $owner1->id];
        $response = $this->getJson(route($this->routeName, $filter))
            ->assertOk();

        $trailers = $response->json('data');
        $this->assertCount(3, $trailers);
    }

    public function test_filter_by_tag(): void
    {
        $this->loginAsCarrierSuperAdmin();

        $tag1 = Tag::factory()->create(['type' => Tag::TYPE_TRUCKS_AND_TRAILER]);
        $tag2 = Tag::factory()->create(['type' => Tag::TYPE_TRUCKS_AND_TRAILER]);

        $trailer1 = factory(Trailer::class)->create();
        $trailer1->tags()->sync([$tag1->id]);

        $trailer2 = factory(Trailer::class)->create();
        $trailer2->tags()->sync([$tag2->id]);

        $trailer3 = factory(Trailer::class)->create();
        $trailer3->tags()->sync([$tag1->id, $tag2->id]);

        $trailer4 = factory(Trailer::class)->create();
        $trailer4->tags()->sync([$tag1->id]);

        $filter = ['tag_id' => $tag1->id];
        $response = $this->getJson(route($this->routeName, $filter))
            ->assertOk();

        $trailers = $response->json('data');
        $this->assertCount(3, $trailers);
    }

    public function test_sort_by_inspection_expiration_date(): void
    {
        $this->loginAsCarrierAdmin();

        $trailer1 = factory(Trailer::class)->create(['inspection_expiration_date' => now()]);
        $trailer2 = factory(Trailer::class)->create(['inspection_expiration_date' => now()->addDays(3)]);
        $trailer3 = factory(Trailer::class)->create(['inspection_expiration_date' => now()->addDays(-5)]);

        $filter = ['order_by' => 'inspection_expiration_date', 'order_type' => 'asc'];
        $response = $this->getJson(route($this->routeName, $filter))
            ->assertOk();

        $this->assertEquals(
            [
                $trailer3->id,
                $trailer1->id,
                $trailer2->id,
            ],
            array_column($response['data'], 'id')
        );

        $filter = ['order_by' => 'inspection_expiration_date', 'order_type' => 'desc'];
        $response = $this->getJson(route($this->routeName, $filter))
            ->assertOk();

        $this->assertEquals(
            [
                $trailer2->id,
                $trailer1->id,
                $trailer3->id,
            ],
            array_column($response['data'], 'id')
        );
    }

    public function test_sort_by_registration_expiration_date(): void
    {
        $this->loginAsCarrierAdmin();

        $trailer1 = factory(Trailer::class)->create(['registration_expiration_date' => now()]);
        $trailer2 = factory(Trailer::class)->create(['registration_expiration_date' => now()->addDays(3)]);
        $trailer3 = factory(Trailer::class)->create(['registration_expiration_date' => now()->addDays(-5)]);

        $filter = ['order_by' => 'registration_expiration_date', 'order_type' => 'asc'];
        $response = $this->getJson(route($this->routeName, $filter))
            ->assertOk();

        $this->assertEquals(
            [
                $trailer3->id,
                $trailer1->id,
                $trailer2->id,
            ],
            array_column($response['data'], 'id')
        );

        $filter = ['order_by' => 'registration_expiration_date', 'order_type' => 'desc'];
        $response = $this->getJson(route($this->routeName, $filter))
            ->assertOk();

        $this->assertEquals(
            [
                $trailer2->id,
                $trailer1->id,
                $trailer3->id,
            ],
            array_column($response['data'], 'id')
        );
    }
}
