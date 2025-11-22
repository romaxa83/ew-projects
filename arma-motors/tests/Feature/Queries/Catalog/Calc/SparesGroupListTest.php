<?php

namespace Tests\Feature\Queries\Catalog\Calc;

use App\Exceptions\ErrorsCode;
use App\Models\Catalogs\Car\Brand;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Mutations\Catalog\Calc\SparesGroupEditTest;
use Tests\Feature\Mutations\Catalog\Calc\SparesGroupToggleActiveTest;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;

class SparesGroupListTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;

    /** @test */
    public function success()
    {
        $admin = $this->adminBuilder()->create();
        $this->loginAsAdmin($admin);

        $response = $this->graphQL($this->getQueryStr());

        $responseData = $response->json('data.sparesGroups');

        $this->assertArrayHasKey('id', $responseData[0]);
        $this->assertArrayHasKey('active', $responseData[0]);
        $this->assertArrayHasKey('type', $responseData[0]);
        $this->assertArrayHasKey('current', $responseData[0]);
        $this->assertArrayHasKey('name', $responseData[0]['current']);
        $this->assertNotEmpty($responseData);
    }

    /** @test */
    public function get_active()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CALC_CATALOG_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $response = $this->graphQL($this->getQueryStrOnlyActive());

        $count = count($response->json('data.sparesGroups'));

        // запрос на переключение первого элемента
        $this->graphQL(SparesGroupToggleActiveTest::getQueryStr(1));

        $newResponse = $this->graphQL($this->getQueryStrOnlyActive());

        $this->assertNotEquals($count, count($newResponse->json('data.sparesGroups')));
        $this->assertEquals($count -1 , count($newResponse->json('data.sparesGroups')));
    }

    /** @test */
    public function get_by_brand()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CALC_CATALOG_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $brand = Brand::find(1);

        $response = $this->graphQL($this->getQueryStrOnlyBrand($brand->id));
        $count = count($response->json('data.sparesGroups'));

        // привязываем бренд
        $data = [
            'id' => 1,
            'brandId' => $brand->id,
        ];
        $this->graphQL(SparesGroupEditTest::getQueryStrOnlyBrand($data));

        $newResponse = $this->graphQL($this->getQueryStrOnlyBrand($brand->id));

        $this->assertNotEquals($count, count($newResponse->json('data.sparesGroups')));
        $this->assertEquals($count + 1, count($newResponse->json('data.sparesGroups')));
    }

    /** @test */
    public function not_auth()
    {
        $response = $this->graphQL($this->getQueryStr());

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not auth'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_AUTH, $response->json('errors.0.extensions.code'));
    }

    public static function getQueryStr(): string
    {
        return  sprintf('{
            sparesGroups {
                id
                active
                type
                current {
                    name
                }
               }
            }'
        );
    }

    public static function getQueryStrOnlyActive(): string
    {
        return  sprintf('{
            sparesGroups (active: true) {
                id
                active
               }
            }'
        );
    }

    public static function getQueryStrOnlyBrand($brandId): string
    {
        return  sprintf('{
            sparesGroups (brandId: %s) {
                id
                active
               }
            }',
        $brandId
        );
    }
}
