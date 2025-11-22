<?php

namespace Tests\Feature\Api\BodyShop\Vehicles\Trailers;

use App\Models\BodyShop\VehicleOwners\VehicleOwner;
use App\Models\Saas\Company\Company;
use App\Models\Tags\Tag;
use App\Models\Vehicles\Trailer;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class TrailerIndexFilterTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    protected string $routeName = 'body-shop.trailers.index';

    public function test_search(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $company = Company::factory()->create(['use_in_body_shop' => true]);

        factory(Trailer::class)->create([
            'vin' => 'TEST123kjhjkh',
            'unit_number' => 'DF12',
            'license_plate' => 'FD1-123',
            'carrier_id' => $company->id,
        ]);

        factory(Trailer::class)->create([
            'vin' => 'HFJ123kjhjkh',
            'unit_number' => 'TE123',
            'license_plate' => 'RFD-234',
            'carrier_id' => null,
        ]);

        factory(Trailer::class)->create([
            'vin' => 'GMGI',
            'unit_number' => 'KG34',
            'license_plate' => 'HFIV-234',
            'carrier_id' => null,
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
        $this->assertCount(2, $trailers);
    }

    public function test_filter_by_tag(): void
    {
        $this->loginAsBodyShopAdmin();

        $tag1 = Tag::factory()->create(['type' => Tag::TYPE_TRUCKS_AND_TRAILER, 'carrier_id' => null]);
        $tag2 = Tag::factory()->create(['type' => Tag::TYPE_TRUCKS_AND_TRAILER]);

        $trailer1 = factory(Trailer::class)->create(['carrier_id' => null]);
        $trailer1->tags()->sync([$tag1->id]);

        $trailer2 = factory(Trailer::class)->create(['carrier_id' => null]);
        $trailer2->tags()->sync([$tag2->id]);

        $trailer3 = factory(Trailer::class)->create(['carrier_id' => null]);
        $trailer3->tags()->sync([$tag1->id, $tag2->id]);

        $trailer4 = factory(Trailer::class)->create(['carrier_id' => null]);
        $trailer4->tags()->sync([$tag1->id]);

        $filter = ['tag_id' => $tag1->id];
        $response = $this->getJson(route($this->routeName, $filter))
            ->assertOk();

        $trailers = $response->json('data');
        $this->assertCount(3, $trailers);
    }

    public function test_sort_by_company_name(): void
    {
        $this->loginAsBodyShopAdmin();

        $company1 = Company::factory()->create([
            'name' => 'BCompany',
            'use_in_body_shop' => true,
        ]);
        $company2 = Company::factory()->create([
            'name' => 'ACompany',
            'use_in_body_shop' => true,
        ]);

        $trailer1 = factory(Trailer::class)->create(['carrier_id' => $company1->id]);
        $trailer2 = factory(Trailer::class)->create(['carrier_id' => $company2->id]);
        $trailer3 = factory(Trailer::class)->create(['carrier_id' => null]);

        $params = ['order_by' => 'company_name', 'order_type' => 'asc'];
        $response = $this->getJson(route($this->routeName, $params))
            ->assertOk();

        $data = $response['data'];

        $this->assertEquals($trailer2->id, $data[0]['id']);
        $this->assertEquals($trailer1->id, $data[1]['id']);
        $this->assertEquals($trailer3->id, $data[2]['id']);

        $params = ['order_by' => 'company_name', 'order_type' => 'desc'];
        $response = $this->getJson(route($this->routeName, $params))
            ->assertOk();

        $data = $response['data'];

        $this->assertEquals($trailer3->id, $data[0]['id']);
        $this->assertEquals($trailer1->id, $data[1]['id']);
        $this->assertEquals($trailer2->id, $data[2]['id']);
    }

    public function test_search_by_customer_owner_name(): void
    {
        $this->loginAsBodyShopSuperAdmin();

        $company = Company::factory()->create(['use_in_body_shop' => true]);
        $customer = factory(VehicleOwner::class)->create(['first_name' => 'firstname']);
        $owner = $this->ownerFactory(['first_name' => 'secondname', 'carrier_id' => $company->id]);

        factory(Trailer::class)->create([
            'carrier_id' => $company->id,
            'owner_id' => $owner->id,
        ]);

        factory(Trailer::class)->create([
            'carrier_id' => null,
            'customer_id' => $customer->id,
        ]);

        factory(Trailer::class)->create([
            'carrier_id' => null,
        ]);

        $filter = ['q' => 'name'];
        $response = $this->getJson(route($this->routeName, $filter))
            ->assertOk();

        $trailers = $response->json('data');
        $this->assertCount(2, $trailers);
    }

    public function test_filter_by_customer(): void
    {
        $this->loginAsBodyShopAdmin();

        $customer1  = factory(VehicleOwner::class)->create();
        $customer2  = factory(VehicleOwner::class)->create();

        factory(Trailer::class)->create(['carrier_id' => null, 'customer_id' => $customer1->id]);
        factory(Trailer::class)->create(['carrier_id' => null, 'customer_id' => $customer1->id]);
        factory(Trailer::class)->create(['carrier_id' => null, 'customer_id' => $customer1->id]);
        factory(Trailer::class)->create(['carrier_id' => null, 'customer_id' => $customer2->id]);

        $filter = ['customer_id' => $customer1->id];
        $response = $this->getJson(route($this->routeName, $filter))
            ->assertOk();

        $trailers = $response->json('data');
        $this->assertCount(3, $trailers);
    }
}
