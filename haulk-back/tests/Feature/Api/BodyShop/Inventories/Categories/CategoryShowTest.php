<?php

namespace Api\BodyShop\Inventories\Categories;

use App\Models\BodyShop\Inventories\Category;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CategoryShowTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_not_show_for_unauthorized_users()
    {
        $category = factory(Category::class)->create();

        $this->getJson(route('body-shop.inventory-categories.show', $category))->assertUnauthorized();
    }

    public function test_it_not_show_for_not_permitted_users()
    {
        $category = factory(Category::class)->create();

        $this->loginAsCarrierAdmin();

        $this->getJson(route('body-shop.inventory-categories.show', $category))
            ->assertForbidden();
    }

    public function test_it_show_for_permitted_users()
    {
        $category = factory(Category::class)->create();

        $this->loginAsBodyShopSuperAdmin();

        $this->getJson(route('body-shop.inventory-categories.show', $category))
            ->assertOk()
            ->assertJsonStructure(['data' => [
                'id',
                'name',
                'hasRelatedEntities',
            ]]);

        $this->loginAsBodyShopAdmin();

        $this->getJson(route('body-shop.inventory-categories.show', $category))
            ->assertOk()
            ->assertJsonStructure(['data' => [
                'id',
                'name',
                'hasRelatedEntities',
            ]]);
    }
}
