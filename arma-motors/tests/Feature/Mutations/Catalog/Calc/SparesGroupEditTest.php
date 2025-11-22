<?php

namespace Tests\Feature\Mutations\Catalog\Calc;

use App\Exceptions\ErrorsCode;
use App\Models\Catalogs\Calc\SparesGroup;
use App\Models\Catalogs\Car\Brand;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\Statuses;

class SparesGroupEditTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;
    use Statuses;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function success()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CALC_CATALOG_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $model = SparesGroup::where('id', 1)->first();
        $brand = Brand::find(1);

        $data = $this->data($model->id);
        $data['brandId'] = $brand->id;

        $this->assertTrue($model->active);
        $this->assertNotEquals($model->sort, $data['sort']);
        $this->assertNotEquals($model->type, SparesGroup::TYPE_QTY);
        $this->assertNotEquals($model->current->name, $data[$model->current->lang]['name']);
        $this->assertNotEquals($model->current->unit, $data[$model->current->lang]['unit']);
        $this->assertNull($model->brand);

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)])
            ->assertOk();

        $responseData = $response->json('data.sparesGroupEdit');

        $this->assertArrayHasKey('active', $responseData);
        $this->assertArrayHasKey('sort', $responseData);
        $this->assertArrayHasKey('type', $responseData);
        $this->assertArrayHasKey('current', $responseData);
        $this->assertArrayHasKey('brand', $responseData);
        $this->assertArrayHasKey('id', $responseData['brand']);
        $this->assertArrayHasKey('lang', $responseData['current']);
        $this->assertArrayHasKey('name', $responseData['current']);
        $this->assertArrayHasKey('unit', $responseData['current']);

        $model->refresh();

        $this->assertFalse($model->active);
        $this->assertEquals($model->sort, $data['sort']);
        $this->assertEquals($model->type, SparesGroup::TYPE_QTY);
        $this->assertEquals($model->current->name, $data[$model->current->lang]['name']);
        $this->assertEquals($model->current->unit, $data[$model->current->lang]['unit']);
        $this->assertNotNull($model->brand);
        $this->assertEquals($model->brand->id, $brand->id);
    }

    /** @test */
    public function success_only_sort()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CALC_CATALOG_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $model = SparesGroup::where('id', 1)->first();

        $data = [
            'id' => $model->id,
            'sort' => 3
        ];

        $this->assertTrue($model->active);
        $this->assertNotEquals($model->sort, $data['sort']);
        $this->assertNull($model->brand);

        $response = $this->postGraphQL(['query' => $this->getQueryStrOnlySort($data)])
            ->assertOk();

        $responseData = $response->json('data.sparesGroupEdit');

        $this->assertArrayHasKey('active', $responseData);
        $this->assertArrayHasKey('sort', $responseData);
        $this->assertArrayHasKey('current', $responseData);
        $this->assertArrayHasKey('lang', $responseData['current']);
        $this->assertArrayHasKey('name', $responseData['current']);
        $this->assertArrayHasKey('brand', $responseData);
        $this->assertEquals($model->id, $responseData['id']);
        $this->assertEquals($model->current->name, $responseData['current']['name']);

        $model->refresh();

        $this->assertTrue($model->active);
        $this->assertEquals($model->sort, $data['sort']);
        $this->assertNull($model->brand);
    }

    /** @test */
    public function success_only_brand()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CALC_CATALOG_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $model = SparesGroup::where('id', 1)->first();
        $brand = Brand::find(1);

        $data = [
            'id' => $model->id,
            'brandId' => $brand->id,
        ];

        $this->assertNull($model->brand);

        $response = $this->postGraphQL(['query' => $this->getQueryStrOnlyBrand($data)])
            ->assertOk();

        $responseData = $response->json('data.sparesGroupEdit');

        $this->assertArrayHasKey('active', $responseData);
        $this->assertArrayHasKey('sort', $responseData);
        $this->assertArrayHasKey('current', $responseData);
        $this->assertArrayHasKey('lang', $responseData['current']);
        $this->assertArrayHasKey('name', $responseData['current']);
        $this->assertArrayHasKey('brand', $responseData);
        $this->assertArrayHasKey('id', $responseData['brand']);

        $model->refresh();

        $this->assertNotNull($model->brand);
        $this->assertEquals($model->brand->name, $brand->name);
    }

    /** @test */
    public function success_only_translation()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CALC_CATALOG_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $model = SparesGroup::where('id', 1)->first();

        $data = [
            'id' => $model->id,
            'ru' => [
                'lang' => 'ru',
                'name' => 'some sparesGroup type ru',
                'unit' => 'some unit type ru',
            ],
            'uk' => [
                'lang' => 'uk',
                'name' => 'some sparesGroup type uk',
                'unit' => 'some unit type uk',
            ],
        ];

        $this->assertNotEquals($model->current->name, $data[$model->current->lang]['name']);
        $this->assertNotEquals($model->current->unit, $data[$model->current->lang]['unit']);

        $response = $this->postGraphQL(['query' => $this->getQueryStrOnlyTranslation($data)])
            ->assertOk();

        $responseData = $response->json('data.sparesGroupEdit');

        $this->assertArrayHasKey('active', $responseData);
        $this->assertArrayHasKey('sort', $responseData);
        $this->assertArrayHasKey('sort', $responseData);
        $this->assertArrayHasKey('current', $responseData);
        $this->assertArrayHasKey('lang', $responseData['current']);
        $this->assertArrayHasKey('name', $responseData['current']);
        $this->assertArrayHasKey('unit', $responseData['current']);

        $model->refresh();

        $this->assertEquals($model->current->name, $responseData['current']['name']);
        $this->assertEquals($model->current->name, $data[$model->current->lang]['name']);
        $this->assertEquals($model->current->unit, $data[$model->current->lang]['unit']);
    }

    /** @test */
    public function not_found_service()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CALC_CATALOG_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $data = [
            'id' => 999,
            'sort' => 3
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStrOnlySort($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('error.not found model'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_FOUND, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function not_auth()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CALC_CATALOG_EDIT)
            ->create();

        $data = ['id' => 1, 'sort' => 3];

        $response = $this->postGraphQL(['query' => $this->getQueryStrOnlySort($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not auth'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_AUTH, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function not_perm()
    {
        $admin = $this->adminBuilder()->createRoleWithPerm(Permissions::CATALOG_BRAND_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $data = ['id' => 1, 'sort' => 3];

        $response = $this->postGraphQL(['query' => $this->getQueryStrOnlySort($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not perm'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_PERM, $response->json('errors.0.extensions.code'));
    }

    private function data(
        $id, $type = null
    ): array
    {
        $type = $type ?? $this->type_spares_group_qty;

        return [
            'id' => $id,
            'sort' => 3,
            'type' => $type,
            'ru' => [
                'lang' => 'ru',
                'name' => 'some sparesGroup ru',
                'unit' => 'some up unit ru',
            ],
            'uk' => [
                'lang' => 'uk',
                'name' => 'some sparesGroup uk',
                'unit' => 'some up unit uk',
            ],
        ];
    }

    private function getQueryStr(array $data): string
    {
        return sprintf('
            mutation {
                sparesGroupEdit(input:{
                    id: "%s",
                    active: false,
                    sort: %d,
                    type: %s,
                    brandId: %s,
                    translations: [
                        {lang: "%s", name: "%s", unit: "%s"}
                        {lang: "%s", name: "%s", unit: "%s"}
                    ]
                }) {
                    active
                    sort
                    type
                    current {
                        lang
                        name
                        unit
                    }
                    brand {
                        id
                    }
                }
            }',
            $data['id'],
            $data['sort'],
            $data['type'],
            $data['brandId'],
            $data['ru']['lang'],
            $data['ru']['name'],
            $data['ru']['unit'],
            $data['uk']['lang'],
            $data['uk']['name'],
            $data['uk']['unit'],
        );
    }

    private function getQueryStrOnlyTranslation(array $data): string
    {
        return sprintf('
            mutation {
                sparesGroupEdit(input:{
                    id: "%s",
                    translations: [
                        {lang: "%s", name: "%s", unit: "%s"}
                        {lang: "%s", name: "%s", unit: "%s"}
                    ]
                }) {
                    active
                    sort
                    current {
                        lang
                        name
                        unit
                    }
                }
            }',
            $data['id'],
            $data['ru']['lang'],
            $data['ru']['name'],
            $data['ru']['unit'],
            $data['uk']['lang'],
            $data['uk']['name'],
            $data['uk']['unit'],
        );
    }

    private function getQueryStrOnlySort(array $data): string
    {
        return sprintf('
            mutation {
                sparesGroupEdit(input:{
                    id: "%s",
                    sort: %d,
                }) {
                    id
                    active
                    sort
                    current {
                        lang
                        name
                    }
                    brand {
                        id
                    }
                }
            }',
            $data['id'],
            $data['sort'],
        );
    }

    public static function getQueryStrOnlyBrand(array $data): string
    {
        return sprintf('
            mutation {
                sparesGroupEdit(input:{
                    id: "%s",
                    brandId: %d,
                }) {
                    id
                    active
                    sort
                    current {
                        lang
                        name
                    }
                    brand {
                        id
                    }
                }
            }',
            $data['id'],
            $data['brandId'],
        );
    }
}




