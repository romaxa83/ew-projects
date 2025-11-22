<?php

namespace Tests\Feature\Mutations\BackOffice\Catalog\Categories;

use App\GraphQL\Mutations\BackOffice\Catalog\Categories\CategoryCreateMutation;
use App\Models\Admins\Admin;
use App\Models\Catalog\Categories\Category;
use App\Permissions\Catalog\Categories;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Arr;
use Tests\Builders\Catalog\CategoryBuilder;
use Tests\TestCase;
use Tests\Traits\Permissions\RoleHelperTrait;
use Tests\Unit\Dto\Catalog\CategoryDtoTest;

class CreateTest extends TestCase
{
    use DatabaseTransactions;
    use RoleHelperTrait;

    public const MUTATION = CategoryCreateMutation::NAME;
    protected array $data = [];

    protected CategoryBuilder $builder;

    public function test_create_as_main(): void
    {
        $this->loginAsSuperAdmin();

        $query = new GraphQLQuery(
            self::MUTATION,
            [
                'active' => true,
                'main' => true,
                'slug' => 'some-unique-slug-to-be-here',
                'translations' => [
                    [
                        'language' => 'es',
                        'title' => 'title es',
                        'description' => 'desc es',
                        'seo_title' => 'custom seo title es',
                        'seo_description' => 'custom seo description es',
                        'seo_h1' => 'custom seo h1 es',
                    ],
                    [
                        'language' => 'en',
                        'title' => 'title en',
                        'description' => 'desc en',
                        'seo_title' => 'custom seo title en',
                        'seo_description' => 'custom seo description en',
                        'seo_h1' => 'custom seo h1 en',
                    ]
                ]
            ],
            [
                'id',
                'sort',
                'active',
                'main',
                'parent' => [
                    'id'
                ],
                'translation' => [
                    'id',
                    'title',
                    'language',
                    'description',
                    'seo_title',
                    'seo_description',
                    'seo_h1',
                ]
            ]
        );

        $this->postGraphQLBackOffice($query->getMutation())
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        self::MUTATION => [
                            'id'
                        ],
                    ]
                ]
            );
    }

    public function test_cannot_create_as_main_with_parent(): void
    {
        $this->loginAsSuperAdmin();

        $query = new GraphQLQuery(
            self::MUTATION,
            [
                'active' => true,
                'main' => true,
                'slug' => 'some-unique-slug',
                'parent_id' => Category::factory()->create()->id,
                'translations' => [
                    [
                        'language' => 'es',
                        'title' => 'title es',
                        'description' => 'desc es',
                    ],
                    [
                        'language' => 'en',
                        'title' => 'title en',
                        'description' => 'desc en',
                    ]
                ]
            ],
            [
                'id',
            ]
        );

        $this->assertServerError(
            $this->postGraphQLBackOffice($query->getMutation()),
            __('Child category cannot be displayed as the main category on the main page')
        );
    }

    /** @test */
    public function success(): void
    {
        $parent = $this->builder->create();
        $this->loginByAdminManager([Categories\CreatePermission::KEY]);

        $data = CategoryDtoTest::data();
        $data['active'] = 'true';
        $data['parent_id'] = $parent->id;

        $res = $this->postGraphQLBackOffice(['query' => $this->getQueryStr($data)]);
        $resData = $res->json(sprintf('data.%s', self::MUTATION));

        $this->assertArrayHasKey('id', $resData);
        $this->assertArrayHasKey('active', $resData);
        $this->assertArrayHasKey('sort', $resData);
        $this->assertArrayHasKey('parent', $resData);
        $this->assertArrayHasKey('id', Arr::get($resData, 'parent'));
        $this->assertArrayHasKey('translation', $resData);
        $this->assertArrayHasKey('id', Arr::get($resData, 'translation'));
        $this->assertArrayHasKey('title', Arr::get($resData, 'translation'));
        $this->assertArrayHasKey('language', Arr::get($resData, 'translation'));
        $this->assertArrayHasKey('description', Arr::get($resData, 'translation'));
        $this->assertArrayHasKey('translations', $resData);
        $this->assertArrayHasKey('id', Arr::get($resData, 'translations.0'));
        $this->assertArrayHasKey('title', Arr::get($resData, 'translations.0'));
        $this->assertArrayHasKey('language', Arr::get($resData, 'translations.0'));
        $this->assertArrayHasKey('description', Arr::get($resData, 'translations.0'));

        $this->assertEquals($parent->id, Arr::get($resData, 'parent.id'));
    }

    protected function loginByAdminManager(array $permissionKey): Admin
    {
        return $this->loginAsAdmin()
            ->assignRole(
                $this->generateRole('Admin manager', $permissionKey, Admin::GUARD)
            );
    }

    protected function getQueryStr(array $data): string
    {
        return sprintf(
            '
            mutation {
                %s (
                    active: %s,
                    parent_id: %s,
                    slug: "%s",
                    translations: [
                        {language: "%s", title: "%s"}
                        {language: "%s", title: "%s"}
                    ]
                ) {
                    id
                    sort
                    active
                    parent {
                        id
                    }
                    translation {
                        id
                        title
                        language
                        description
                    }
                    translations {
                        id
                        title
                        language
                        description
                    }
                }
            }',
            self::MUTATION,
            $data['active'],
            $data['parent_id'],
            $data['slug'],
            $data['translations']['es']['language'],
            $data['translations']['es']['title'],
            $data['translations']['en']['language'],
            $data['translations']['en']['title'],
        );
    }

    /** @test */
    public function success_without_parent(): void
    {
        $this->loginByAdminManager([Categories\CreatePermission::KEY]);

        $data = CategoryDtoTest::data();
        $data['active'] = 'true';
        $data['parent_id'] = 'null';

        $res = $this->postGraphQLBackOffice(['query' => $this->getQueryStr($data)]);
        $resData = $res->json(sprintf('data.%s', self::MUTATION));

        $this->assertArrayHasKey('parent', $resData);
        $this->assertNull(Arr::get($resData, 'parent.id'));
    }

    /** @test */
    public function not_perm(): void
    {
        $this->loginByAdminManager([Categories\UpdatePermission::KEY]);

        $data = CategoryDtoTest::data();
        $data['active'] = 'true';
        $data['parent_id'] = 'null';

        $res = $this->postGraphQLBackOffice(['query' => $this->getQueryStr($data)]);

        $this->assertArrayHasKey('errors', $res->json());
        $this->assertEquals('No permission', $res->json('errors.0.message'));
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->builder = app(CategoryBuilder::class);
    }
}
