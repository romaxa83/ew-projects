<?php

namespace Api\Tags;

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
        $this->loginAsCarrierSuperAdmin();

        $tag1 = Tag::factory()->create([
            'name' => 'Name1',
        ]);

        $tag2 = Tag::factory()->create([
            'name' => 'Name2',
        ]);

        $tag3 = Tag::factory()->create([
            'name' => 'Name3',
        ]);


        $filter = ['q' => 'Name3'];
        $response = $this->getJson(route('tags.index', $filter))
            ->assertOk();

        $tags = $response->json('data');
        $this->assertCount(1, $tags['order']);
        $this->assertEquals($tag3->id, $tags['order'][0]['id']);
    }
}
