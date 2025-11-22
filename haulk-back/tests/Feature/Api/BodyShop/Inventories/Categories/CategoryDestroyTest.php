<?php

namespace Api\BodyShop\Inventories\Categories;

use App\Models\BodyShop\Inventories\Category;
use App\Models\BodyShop\Inventories\Inventory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\TestCase;

class CategoryDestroyTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_not_delete_for_unauthorized_users()
    {
        $category = factory(Category::class)->create();

        $this->deleteJson(route('body-shop.inventory-categories.destroy', $category))->assertUnauthorized();
    }

    public function test_it_not_delete_for_not_permitted_users()
    {
        $category = factory(Category::class)->create();

        $this->loginAsCarrierDispatcher();

        $this->deleteJson(route('body-shop.inventory-categories.destroy', $category))
            ->assertForbidden();
    }

    public function test_it_delete_by_bs_super_admin()
    {
        $category = factory(Category::class)->create();

        $this->assertDatabaseHas(Category::TABLE_NAME, $category->getAttributes());

        $this->loginAsBodyShopSuperAdmin();
        $this->deleteJson(route('body-shop.inventory-categories.destroy', $category))
            ->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseMissing(Category::TABLE_NAME, $category->getAttributes());
    }

    public function test_it_delete_by_bs_admin()
    {
        $category = factory(Category::class)->create();

        $this->assertDatabaseHas(Category::TABLE_NAME, $category->getAttributes());

        $this->loginAsBodyShopAdmin();
        $this->deleteJson(route('body-shop.inventory-categories.destroy', $category))
            ->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseMissing(Category::TABLE_NAME, $category->getAttributes());
    }

    public function test_cant_delete_with_relation()
    {
        $category = factory(Category::class)->create();
        factory(Inventory::class)->create(['category_id' => $category]);

        $this->assertDatabaseHas(Category::TABLE_NAME, $category->getAttributes());

        $this->loginAsBodyShopAdmin();
        $this->deleteJson(route('body-shop.inventory-categories.destroy', $category))
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertDatabaseHas(Category::TABLE_NAME, $category->getAttributes());
    }
}
