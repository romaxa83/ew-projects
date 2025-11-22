<?php

namespace Tests\Feature\Api\BodyShop\Inventories\Categories;

use App\Models\BodyShop\Inventories\Category;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Helpers\Traits\UserFactoryHelper;
use Tests\TestCase;

class CategoryIndexFilterTest extends TestCase
{
    use DatabaseTransactions;
    use UserFactoryHelper;

    public function test_search()
    {
        $this->loginAsBodyShopSuperAdmin();

        $category1 = factory(Category::class)->create([
            'name' => 'Name1',
        ]);

        $category2 = factory(Category::class)->create([
            'name' => 'Name2',
        ]);

        $category3 = factory(Category::class)->create([
            'name' => 'Name3',
        ]);


        $filter = ['q' => 'Name3'];
        $response = $this->getJson(route('body-shop.inventory-categories.index', $filter))
            ->assertOk();

        $categories = $response->json('data');
        $this->assertCount(1, $categories);
        $this->assertEquals($category3->id, $categories[0]['id']);
    }

    public function test_sort()
    {
        $this->loginAsBodyShopSuperAdmin();

        $category1 = factory(Category::class)->create([
            'name' => 'Name1',
        ]);

        $category2 = factory(Category::class)->create([
            'name' => 'Name2',
        ]);

        $category3 = factory(Category::class)->create([
            'name' => 'Name3',
        ]);


        $params = ['order_by' => 'name', 'order_type' => 'desc'];
        $response = $this->getJson(route('body-shop.inventory-categories.index', $params))
            ->assertOk();

        $categories = $response->json('data');
        $this->assertEquals($category1->id, $categories[2]['id']);
        $this->assertEquals($category2->id, $categories[1]['id']);
        $this->assertEquals($category3->id, $categories[0]['id']);
    }
}
