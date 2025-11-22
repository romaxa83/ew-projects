<?php

namespace Tests\Feature\Queries\BackOffice\Catalog\Products;

use App\Enums\Catalog\Products\ProductUnitType;
use App\GraphQL\Queries\BackOffice\Catalog\Products;
use App\Models\Admins\Admin;
use App\Models\Catalog\Products\Product;
use App\Models\Catalog\Products\UnitType;
use App\Permissions\Catalog\Products as ProductPerm;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Arr;
use Tests\Builders\Catalog\CategoryBuilder;
use Tests\Builders\Catalog\ProductBuilder;
use Tests\TestCase;
use Tests\Traits\Permissions\RoleHelperTrait;

class ForAdminPanelTest extends TestCase
{
    use DatabaseTransactions;
    use RoleHelperTrait;

    protected ProductBuilder $builder;
    protected CategoryBuilder $builderCategory;

    public const QUERY = Products\ProductsQuery::NAME;

    protected function loginByAdminManager(array $permissionKey): Admin
    {
        return $this->loginAsAdmin()
            ->assignRole(
                $this->generateRole('Admin manager', $permissionKey, Admin::GUARD)
            );
    }
    protected function setUp(): void
    {
        parent::setUp();

        $this->builder = app(ProductBuilder::class);
        $this->builderCategory = app(CategoryBuilder::class);
    }

    /** @test */
    public function list_models(): void
    {
        $category = $this->builderCategory->create();
        Product::factory()->times(30)->create(['category_id' => $category->id]);

        $this->loginByAdminManager([ProductPerm\ListPermission::KEY]);

        $res = $this->postGraphQLBackOffice(['query' => $this->getQueryStrPerPage(10)]);

        $resData = $res->json(sprintf('data.%s.data', self::QUERY));
        $this->assertCount(10, $resData);
    }

    /** @test */
    public function list_filter_by_active(): void
    {
        $model = $this->builder->setActive(false)->create();
        $this->builder->create();
        $this->builder->create();

        $this->loginByAdminManager([ProductPerm\ListPermission::KEY]);

        $res = $this->postGraphQLBackOffice(['query' => $this->getQueryStrActive('false')]);

        $resData = $res->json(sprintf('data.%s.data', self::QUERY));
        $this->assertCount(1, $resData);
        $this->assertEquals($model->id, Arr::get($resData, '0.id'));

        $this->postGraphQLBackOffice(['query' => $this->getQueryStrActive('true')])
            ->assertJsonStructure([
                'data' => [
                    self::QUERY => [
                        'data' => [['id']]
                    ]
                ]
            ]);
    }

    /** @test */
    public function list_filter_by_category_id(): void
    {
        $category = $this->builderCategory->create();
        $anotherCategory = $this->builderCategory->create();
        $this->builder->setCategoryId($category->id)->create();
        $this->builder->setCategoryId($category->id)->create();
        $this->builder->setCategoryId($category->id)->create();
        $this->builder->setCategoryId($anotherCategory->id)->create();

        $this->loginByAdminManager([ProductPerm\ListPermission::KEY]);

        $res = $this->postGraphQLBackOffice(['query' => $this->getQueryStrCategoryId($category->id)]);

        $resData = $res->json(sprintf('data.%s.data', self::QUERY));
        $this->assertCount(3, $resData);

        $res = $this->postGraphQLBackOffice(['query' => $this->getQueryStrCategoryId($anotherCategory->id)]);
        $resData = $res->json(sprintf('data.%s.data', self::QUERY));
        $this->assertCount(1, $resData);
    }

    /** @test */
    public function list_filter_by_title(): void
    {
        $title = 'Search';
        $model = $this->builder->withTranslation()->setTitle($title)->create();
        $this->builder->withTranslation()->create();
        $this->builder->withTranslation()->create();

        $this->loginByAdminManager([ProductPerm\ListPermission::KEY]);

        $res = $this->postGraphQLBackOffice(['query' => $this->getQueryStrTitle($title)]);

        $resData = $res->json(sprintf('data.%s.data', self::QUERY));
        $this->assertCount(1, $resData);
        $this->assertEquals($model->id, Arr::get($resData, '0.id'));
    }

    /** @test */
    public function filter_by_unit_type(): void
    {
        $unitTypeAccessory = UnitType::query()->where('name', ProductUnitType::ACCESSORY())->first();
        $unitTypeIndoor = UnitType::query()->where('name', ProductUnitType::INDOOR())->first();
        $unitTypeOutdoor = UnitType::query()->where('name', ProductUnitType::OUTDOOR())->first();

        $this->assertNotNull($unitTypeOutdoor);
        $this->assertNotNull($unitTypeIndoor);
        $this->assertNotNull($unitTypeAccessory);

        $product_1 = $this->builder->setUnitTypeId($unitTypeAccessory->id)->create();
        $product_2 = $this->builder->setUnitTypeId($unitTypeAccessory->id)->create();
        $product_3 = $this->builder->setUnitTypeId($unitTypeAccessory->id)->create();
        $product_4 = $this->builder->setUnitTypeId($unitTypeIndoor->id)->create();
        $product_5 = $this->builder->setUnitTypeId($unitTypeOutdoor->id)->create();
        $product_6 = $this->builder->setUnitTypeId($unitTypeOutdoor->id)->create();

        $this->loginByAdminManager([ProductPerm\ListPermission::KEY]);

        $this->postGraphQLBackOffice(['query' => $this->getQueryStrUnitType(ProductUnitType::ACCESSORY())])
            ->assertJson([
                'data' => [
                    self::QUERY => [
                        'data' => [
                            ['unitType' => ['id' => $unitTypeAccessory->id], 'unit_sub_type' => null],
                            ['unitType' => ['id' => $unitTypeAccessory->id], 'unit_sub_type' => null],
                            ['unitType' => ['id' => $unitTypeAccessory->id], 'unit_sub_type' => null],
                        ]
                    ]
                ]
            ])
            ->assertJsonCount(3, 'data.'.self::QUERY.'.data')
        ;
    }

    /** @test */
    public function filter_by_unit_type_wrong_type(): void
    {
        $unitTypeAccessory = UnitType::query()->where('name', ProductUnitType::ACCESSORY())->first();
        $unitTypeIndoor = UnitType::query()->where('name', ProductUnitType::INDOOR())->first();
        $unitTypeOutdoor = UnitType::query()->where('name', ProductUnitType::OUTDOOR())->first();

        $this->assertNotNull($unitTypeOutdoor);
        $this->assertNotNull($unitTypeIndoor);
        $this->assertNotNull($unitTypeAccessory);

        $product_1 = $this->builder->setUnitTypeId($unitTypeAccessory->id)->create();
        $product_2 = $this->builder->setUnitTypeId($unitTypeAccessory->id)->create();
        $product_3 = $this->builder->setUnitTypeId($unitTypeAccessory->id)->create();
        $product_4 = $this->builder->setUnitTypeId($unitTypeIndoor->id)->create();
        $product_5 = $this->builder->setUnitTypeId($unitTypeOutdoor->id)->create();
        $product_6 = $this->builder->setUnitTypeId($unitTypeOutdoor->id)->create();

        $this->loginByAdminManager([ProductPerm\ListPermission::KEY]);

        $this->postGraphQLBackOffice(['query' => $this->getQueryStrUnitType('wrong')])
            ->assertJson([
                'errors' => [
                    [
                        'message' => 'Field "products" argument "unit_type" requires type ProductUnitTypeTypeEnumType, found wrong.'
                    ]
                ]
            ])
        ;
    }

    /** @test */
    public function not_perm(): void
    {
        $category = $this->builderCategory->create();
        Product::factory()->times(30)->create(['category_id' => $category->id]);

        $this->loginByAdminManager([ProductPerm\CreatePermission::KEY]);

        $res = $this->postGraphQLBackOffice(['query' => $this->getQueryStrPerPage(10)]);

        $this->assertArrayHasKey('errors', $res->json());
        $this->assertEquals('No permission', $res->json('errors.0.message'));
    }

    private function getQueryStrPerPage(int $perPage): string
    {
        return sprintf('
            query {
                %s (per_page: %d) {
                    data {
                        id
                    }
                }
            }',
            self::QUERY,
            $perPage
        );
    }

    private function getQueryStrActive($active): string
    {
        return sprintf('
            query {
                %s (active: %s) {
                    data {
                        id
                    }
                }
            }',
            self::QUERY,
            $active
        );
    }

    private function getQueryStrCategoryId(int $id): string
    {
        return sprintf('
            query {
                %s (category_id: %s) {
                    data {
                        id
                        category {
                            id
                        }
                    }
                }
            }',
            self::QUERY,
            $id
        );
    }

    private function getQueryStrTitle(string $title): string
    {
        return sprintf('
            query {
                %s (title: "%s") {
                    data {
                        id
                    }
                }
            }',
            self::QUERY,
            $title
        );
    }

    private function getQueryStrUnitType(string $unitType): string
    {
        return sprintf('
            query {
                %s (unit_type: %s) {
                    data {
                        unitType {
                            id
                        }
                        unit_sub_type
                    }
                }
            }',
            self::QUERY,
            $unitType
        );
    }
}

