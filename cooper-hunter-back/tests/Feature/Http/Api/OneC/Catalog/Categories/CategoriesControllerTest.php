<?php

namespace Tests\Feature\Http\Api\OneC\Catalog\Categories;

use App\Http\Controllers\Api\OneC\Catalog\CategoriesController;
use App\Models\Catalog\Categories\Category;
use App\Models\Catalog\Categories\CategoryTranslation;
use App\Models\OneC\Moderator;
use App\Permissions\Catalog\Categories\DeletePermission;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;
use Tests\Traits\Permissions\RoleHelperTrait;

/**
 * @see CategoriesController::class
 */
class CategoriesControllerTest extends TestCase
{
    use DatabaseTransactions;
    use RoleHelperTrait;
    use WithFaker;

    public function test_unauthorized(): void
    {
        $this->getJson(route('1c.categories.index'))
            ->assertUnauthorized();
    }

    public function test_no_permission(): void
    {
        $role = $this->generateRole(
            'Wrong permission role',
            [DeletePermission::KEY],
            Moderator::GUARD
        );

        $this->loginAsModerator(role: $role);

        $this->getJson(route('1c.categories.index'))
            ->assertForbidden();
    }

    public function test_index(): void
    {
        $this->loginAsModerator();

        Category::factory()
            ->times(10)
            ->has(
                CategoryTranslation::factory()->allLocales(),
                'translations'
            )
            ->create();

        $this->getJson(route('1c.categories.index'))
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        $this->getJsonStructure()
                    ],
                ],
            );
    }

    protected function getJsonStructure(): array
    {
        return [
            'id',
            'guid',
            'active',
            'parent_id',
            'parent_guid',
            'translations' => [
                [
                    'id',
                    'title',
                    'description',
                    'language',
                ],
            ],
        ];
    }

    public function test_show(): void
    {
        $this->loginAsModerator();

        $category = Category::factory()
            ->has(
                CategoryTranslation::factory()->allLocales(),
                'translations'
            )
            ->create();

        $this->getJson(route('1c.categories.show', $category->guid))
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => $this->getJsonStructure()
                ]
            );
    }

    public function test_show_not_found(): void
    {
        $this->loginAsModerator();

        $this->getJson(route('1c.categories.show', 0))
            ->assertNotFound();
    }

    public function test_create_parent(): void
    {
        $this->loginAsModerator();

        $this->postJson(
            route('1c.categories.store'),
            array_merge(
                $this->getParams(),
                ['guid' => $this->faker->uuid]
            )
        )
            ->assertCreated()
            ->assertJsonStructure(
                [
                    'data' => $this->getJsonStructure()
                ]
            );
    }

    protected function getParams(string $parentGuid = null): array
    {
        return [
            'active' => true,
            'parent_guid' => $parentGuid,
            'slug' => 'some-slug',
            'translations' => [
                [
                    'language' => 'en',
                    'title' => 'en title',
                    'description' => 'en description',
                ],
                [
                    'language' => 'es',
                    'title' => 'es title',
                    'description' => 'es description',
                ]
            ],
        ];
    }

    public function test_create_child(): void
    {
        $this->loginAsModerator();

        $parent = Category::factory()->create();

        $this->postJson(
            route('1c.categories.store'),
            array_merge(
                $this->getParams($parent->guid),
                ['guid' => $this->faker->uuid]
            )
        )
            ->assertCreated()
            ->assertJsonStructure(
                [
                    'data' => $this->getJsonStructure()
                ]
            );
    }

    public function test_update_incomplete_translations(): void
    {
        $this->loginAsModerator();

        $category = Category::factory()
            ->has(
                CategoryTranslation::factory()->allLocales(),
                'translations'
            )
            ->create();

        $this->putJson(
            route('1c.categories.update', $category->guid),
            [
                'active' => true,
                'parent_id' => $category->parent_id,
                'translations' => [
                    [
                        'language' => 'en',
                        'title' => 'en title',
                        'description' => 'en description',
                    ]
                ],
            ]
        )->assertUnprocessable();
    }

    public function test_update(): void
    {
        $this->loginAsModerator();

        $category = Category::factory()
            ->has(
                CategoryTranslation::factory()->allLocales(),
                'translations'
            )
            ->create();

        $this->putJson(
            route('1c.categories.update', $category->guid),
            $this->getParams()
        )
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => $this->getJsonStructure()
                ]
            );
    }

    public function test_delete(): void
    {
        $this->loginAsModerator();

        $category = Category::factory()->create();

        $this->deleteJson(route('1c.categories.destroy', $category->guid))
            ->assertOk();
    }

    public function test_update_guid(): void
    {
        $this->loginAsModerator();

        $category = Category::factory()->create(['guid' => null]);
        $guid = Uuid::uuid4();

        $this->assertDatabaseMissing(Category::TABLE, ['guid' => $guid]);

        $this->postJson(
            route('1c.categories.update.guid'),
            [
                'data' => [
                    [
                        'id' => $category->id,
                        'guid' => $guid,
                    ]
                ]
            ]
        )->assertJsonStructure(
            [
                'data' => [
                    [
                        'id',
                        'guid',
                    ]
                ],
            ]
        );

        $this->assertDatabaseHas(Category::TABLE, ['guid' => $guid]);
    }
}
