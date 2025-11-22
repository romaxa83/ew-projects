<?php

namespace Api\BodyShop\Inventories\Categories;

use App\Models\BodyShop\Inventories\Category;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\TestCase;

class CategoryCreateTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_forbidden_to_users_create_for_not_authorized_users()
    {
        $this->postJson(route('body-shop.inventory-categories.store'), [])->assertUnauthorized();
    }

    public function test_it_create()
    {
        $this->loginAsBodyShopSuperAdmin();

        $formRequest = [
            'name' => 'Name Test',
        ];

        $this->assertDatabaseMissing(Category::TABLE_NAME, $formRequest);

        $this->postJson(route('body-shop.inventory-categories.store'), $formRequest)
            ->assertCreated();

        $this->assertDatabaseHas(Category::TABLE_NAME, $formRequest);
    }

    public function test_it_validation_messages()
    {
        $this->loginAsBodyShopSuperAdmin();

        $this->postJson(route('body-shop.inventory-categories.store'), [])
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJson(
                [
                    'errors' => [
                        [
                            'source' => ['parameter' => 'name'],
                            'title' => 'The Name field is required.',
                            'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                        ]
                    ],
                ]
            );
    }

    public function test_unique_validation(): void
    {
        $this->loginAsBodyShopSuperAdmin();
        $name = 'Test name';
        factory(Category::class)->create(['name' => $name]);

        $formRequest = [
            'name' => $name,
        ];

        $this->postJson(route('body-shop.inventory-categories.store'), $formRequest)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
