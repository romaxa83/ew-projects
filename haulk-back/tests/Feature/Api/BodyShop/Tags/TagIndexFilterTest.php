<?php

namespace Api\BodyShop\Tags;

use App\Models\Tags\Tag;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class TagIndexFilterTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    public function test_search()
    {
        $this->loginAsBodyShopAdmin();

        $tag1 = Tag::factory()->create([
            'type' => Tag::TYPE_VEHICLE_OWNER,
            'name' => 'Name1',
            'carrier_id' => null,
        ]);

        $tag2 = Tag::factory()->create([
            'type' => Tag::TYPE_VEHICLE_OWNER,
            'name' => 'Name2',
            'carrier_id' => null,
        ]);

        $tag3 = Tag::factory()->create([
            'type' => Tag::TYPE_VEHICLE_OWNER,
            'name' => 'Name3',
            'carrier_id' => null,
        ]);


        $filter = ['q' => 'Name3'];
        $response = $this->getJson(route('body-shop.tags.index', $filter))
            ->assertOk();

        $tags = $response->json('data');
        $this->assertCount(1, $tags['vehicle_owner']);
        $this->assertEquals($tag3->id, $tags['vehicle_owner'][0]['id']);
    }
}
