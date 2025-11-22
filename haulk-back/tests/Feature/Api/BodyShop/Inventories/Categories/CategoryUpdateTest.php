<?php

namespace Api\BodyShop\Inventories\Categories;

use App\Models\BodyShop\Inventories\Category;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\TestCase;

class CategoryUpdateTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_not_update_for_unauthorized_users()
    {
        $category = factory(Category::class)->create();

        $this->putJson(route('body-shop.inventory-categories.update', $category))->assertUnauthorized();
    }

    public function test_it_update_by_bs_super_admin()
    {
        $category = factory(Category::class)->create();

        $formRequest = [
            'name' => 'Name Test',
        ];

        $this->assertDatabaseMissing(Category::TABLE_NAME, $formRequest);

        $this->loginAsBodyShopSuperAdmin();

        $this->putJson(route('body-shop.inventory-categories.update', $category), $formRequest)
            ->assertOk();

        $this->assertDatabaseHas(Category::TABLE_NAME, $formRequest);
    }

    public function test_unique_validation()
    {
        $this->loginAsBodyShopSuperAdmin();
        $name = 'TestName';
        factory(Category::class)->create(['name' => $name]);
        $category = factory(Category::class)->create();

        $formRequest = [
            'name' => $name,
        ];

        $this->putJson(route('body-shop.inventory-categories.update', $category), $formRequest)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
