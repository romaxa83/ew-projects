<?php

namespace Tests\Feature\Queries\BackOffice\Catalog\Categories;

use App\GraphQL\Queries\BackOffice\Catalog\Categories;
use App\Models\Admins\Admin;
use App\Models\Catalog\Categories\Category;
use App\Permissions\Catalog\Categories as CategoryPerm;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Arr;
use Tests\Builders\Catalog\CategoryBuilder;
use Tests\TestCase;
use Tests\Traits\Permissions\RoleHelperTrait;

class ForAdminPanelTest extends TestCase
{
    use DatabaseTransactions;
    use RoleHelperTrait;

    public const QUERY = Categories\CategoriesQuery::NAME;
    protected CategoryBuilder $builder;

    /** @test */
    public function list_category(): void
    {
        Category::factory()->times(5)->create();

        $this->loginByAdminManager([CategoryPerm\ListPermission::KEY]);

        $res = $this->postGraphQLBackOffice(['query' => $this->getQueryStr()]);

        $resData = $res->json(sprintf('data.%s', self::QUERY));
        $this->assertCount(Category::query()->cooper()->whereNull('parent_id')->count(), $resData);
    }

    /** @test */
    public function list_category_with_olmo(): void
    {
        Category::factory()->times(5)->create();

        $this->loginByAdminManager([CategoryPerm\ListPermission::KEY]);

        $res = $this->postGraphQLBackOffice(['query' => $this->getQueryStrWithOlmo()]);

        $resData = $res->json(sprintf('data.%s', self::QUERY));
        $this->assertCount(Category::query()->whereNull('parent_id')->count(), $resData);
    }

    protected function loginByAdminManager(array $permissionKey): Admin
    {
        return $this->loginAsAdmin()
            ->assignRole(
                $this->generateRole('Admin manager', $permissionKey, Admin::GUARD)
            );
    }

    private function getQueryStr(): string
    {
        return sprintf(
            '
            query {
                %s {
                    id
                }
            }',
            self::QUERY,
        );
    }

    private function getQueryStrWithOlmo(): string
    {
        return sprintf(
            '
            query  {
                %s (with_olmo: true) {
                    id
                }
            }',
            self::QUERY,
        );
    }

    /** @test */
    public function list_filter_by_active(): void
    {
        $model = $this->builder->setActive(false)->create();
        $this->builder->create();
        $this->builder->create();

        $this->loginByAdminManager([CategoryPerm\ListPermission::KEY]);

        $res = $this->postGraphQLBackOffice(['query' => $this->getQueryStrActive('false')]);

        $resData = $res->json(sprintf('data.%s', self::QUERY));
        $this->assertCount(1, $resData);
        $this->assertEquals($model->id, Arr::get($resData, '0.id'));

        $this->postGraphQLBackOffice(['query' => $this->getQueryStrActive('true')])
            ->assertJsonStructure([
                'data' => [
                    self::QUERY => [
                        ['id']
                    ]
                ]
            ]);
    }

    private function getQueryStrActive($active): string
    {
        return sprintf(
            '
            query {
                %s (active: %s) {
                    id
                }
            }',
            self::QUERY,
            $active
        );
    }

    /** @test */
    public function list_filter_by_parent_id(): void
    {
        $parent = $this->builder->create();
        $this->builder->setParentId($parent->id)->create();
        $this->builder->setParentId($parent->id)->create();
        $this->builder->setParentId($parent->id)->create();
        $this->builder->create();

        $this->loginByAdminManager([CategoryPerm\ListPermission::KEY]);

        $res = $this->postGraphQLBackOffice(['query' => $this->getQueryStrParentId($parent->id)]);

        $resData = $res->json(sprintf('data.%s', self::QUERY));
        $this->assertCount(3, $resData);
    }

    private function getQueryStrParentId(int $id): string
    {
        return sprintf(
            '
            query {
                %s (parent_id: %s) {
                    id
                    parent {
                        id
                    }
                }
            }',
            self::QUERY,
            $id
        );
    }

    /** @test */
    public function list_filter_by_title(): void
    {
        $title = 'Search';
        $parent = $this->builder->create();
        $model = $this->builder->withTranslation()->setTitle($title)->create();
        $this->builder->withTranslation()->setParentId($parent->id)->create();
        $this->builder->withTranslation()->setParentId($parent->id)->create();
        $this->builder->withTranslation()->create();

        $this->loginByAdminManager([CategoryPerm\ListPermission::KEY]);

        $res = $this->postGraphQLBackOffice(['query' => $this->getQueryStrTitle($title)]);

        $resData = $res->json(sprintf('data.%s', self::QUERY));
        $this->assertCount(1, $resData);
        $this->assertEquals($model->id, Arr::get($resData, '0.id'));
    }

    private function getQueryStrTitle(string $title): string
    {
        return sprintf(
            '
            query {
                %s (title: "%s") {
                    id
                }
            }',
            self::QUERY,
            $title
        );
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->builder = app(CategoryBuilder::class);
    }
}
