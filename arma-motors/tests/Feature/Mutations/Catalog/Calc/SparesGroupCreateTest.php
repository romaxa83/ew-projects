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

class SparesGroupCreateTest extends TestCase
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
            ->createRoleWithPerm(Permissions::CALC_CATALOG_CREATE)
            ->create();
        $this->loginAsAdmin($admin);

        $brand = Brand::find(1);

        $data = $this->data();
        $data['sort'] = 5;
        $data['type'] = $this->type_spares_group_volume;
        $data['brandId'] = $brand->id;

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)])
            ->assertOk();

        $responseData = $response->json('data.sparesGroupCreate');

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('active', $responseData);
        $this->assertArrayHasKey('sort', $responseData);
        $this->assertArrayHasKey('type', $responseData);
        $this->assertArrayHasKey('current', $responseData);
        $this->assertArrayHasKey('lang', $responseData['current']);
        $this->assertArrayHasKey('name', $responseData['current']);
        $this->assertArrayHasKey('unit', $responseData['current']);
        $this->assertArrayHasKey('brand', $responseData);
        $this->assertArrayHasKey('id', $responseData['brand']);
        $this->assertArrayHasKey('name', $responseData['brand']);

        $this->assertEquals($data['sort'], $responseData['sort']);
        $this->assertEquals($data[$responseData['current']['lang']]['name'], $responseData['current']['name']);
        $this->assertEquals($data[$responseData['current']['lang']]['unit'], $responseData['current']['unit']);

        $model = SparesGroup::where('id', $responseData['id'])->first();

        $this->assertNotNull($model);
        $this->assertEquals($model->current->name, $responseData['current']['name']);
        $this->assertEquals($model->current->unit, $responseData['current']['unit']);
        $this->assertEquals($model->brand->id, $brand->id);
        $this->assertEquals($model->brand->name, $brand->name);
    }

    /** @test */
    public function success_only_required_field()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CALC_CATALOG_CREATE)
            ->create();
        $this->loginAsAdmin($admin);

        $data = $this->data();
        $data['type'] = $this->type_spares_group_qty;

        $response = $this->postGraphQL(['query' => $this->getQueryStrOnlyRequired($data)])
            ->assertOk();

        $responseData = $response->json('data.sparesGroupCreate');

        $this->assertArrayHasKey('active', $responseData);
        $this->assertArrayHasKey('type', $responseData);
        $this->assertArrayHasKey('sort', $responseData);
        $this->assertArrayHasKey('current', $responseData);
        $this->assertArrayHasKey('lang', $responseData['current']);
        $this->assertArrayHasKey('name', $responseData['current']);
        $this->assertArrayHasKey('unit', $responseData['current']);
        $this->assertArrayHasKey('brand', $responseData);

        $this->assertNull($responseData['brand']);
        $model = SparesGroup::where('id', $responseData['id'])->first();

        $this->assertNotNull($model);
        $this->assertEquals($model->current->name, $responseData['current']['name']);
        $this->assertEquals($model->current->unit, $responseData['current']['unit']);
    }

    /** @test */
    public function not_auth()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CALC_CATALOG_CREATE)
            ->create();

        $data = $this->data();
        $data['type'] = $this->type_spares_group_qty;

        $response = $this->postGraphQL(['query' => $this->getQueryStrOnlyRequired($data)]);

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

        $data = $this->data();
        $data['type'] = $this->type_spares_group_qty;

        $response = $this->postGraphQL(['query' => $this->getQueryStrOnlyRequired($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not perm'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_PERM, $response->json('errors.0.extensions.code'));
    }

    private function data(): array
    {
        return [
            'ru' => [
                'lang' => 'ru',
                'name' => 'some sparesGroupCreate ru',
                'unit' => 'some unit ru',
            ],
            'uk' => [
                'lang' => 'uk',
                'name' => 'some sparesGroupCreate uk',
                'unit' => 'some unit uk',
            ],
        ];
    }

    private function getQueryStr(array $data): string
    {
        return sprintf('
            mutation {
                sparesGroupCreate(input:{
                    active: true,
                    sort: %d,
                    type: %s,
                    brandId: %s,
                    translations: [
                        {lang: "%s", name: "%s", unit: "%s"}
                        {lang: "%s", name: "%s", unit: "%s"}
                    ]
                }) {
                    id
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
                        name
                    }
                }
            }',
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

    private function getQueryStrOnlyRequired(array $data): string
    {
        return sprintf('
            mutation {
                sparesGroupCreate(input:{
                    type: %s,
                    translations: [
                        {lang: "%s", name: "%s", unit: "%s"}
                        {lang: "%s", name: "%s", unit: "%s"}
                    ]
                }) {
                    id
                    active
                    type
                    sort
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
            $data['type'],
            $data['ru']['lang'],
            $data['ru']['name'],
            $data['ru']['unit'],
            $data['uk']['lang'],
            $data['uk']['name'],
            $data['uk']['unit'],
        );
    }
}
