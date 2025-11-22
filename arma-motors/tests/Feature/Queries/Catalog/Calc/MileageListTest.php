<?php

namespace Tests\Feature\Queries\Catalog\Calc;

use App\Exceptions\ErrorsCode;
use App\Models\Catalogs\Calc\Mileage;
use App\Models\Catalogs\Car\Brand;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Mutations\Catalog\Calc\MileageToggleActiveTest;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\Builders\CalcCatalogBuilder;

class MileageListTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;
    use CalcCatalogBuilder;

    /** @test */
    public function success()
    {
        $admin = $this->adminBuilder()->create();
        $this->loginAsAdmin($admin);

        $total = Mileage::count();

        $response = $this->graphQL($this->getQueryStr());

        $responseData = $response->json('data.mileages');

        $this->assertNotEmpty($responseData);
        $this->assertCount($total, $responseData);
        $this->assertArrayHasKey('id', $responseData[0]);
        $this->assertArrayHasKey('active', $responseData[0]);
    }

    /** @test */
    public function get_active()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CALC_CATALOG_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $response = $this->graphQL($this->getQueryStrOnlyActive());

        $count = count($response->json('data.mileages'));

        // запрос на переключение первого элемента
        $this->graphQL(MileageToggleActiveTest::getQueryStr(1));

        $newResponse = $this->graphQL($this->getQueryStrOnlyActive());

        $this->assertNotEquals($count, count($newResponse->json('data.mileages')));
        $this->assertEquals($count -1 , count($newResponse->json('data.mileages')));
    }

    /** @test */
    public function get_by_brand()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CALC_CATALOG_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $brandID = 1;

        // нет привязанных брендов
        $response = $this->graphQL($this->getQueryStrOnlyBrand($brandID));
        $this->assertEmpty($response->json('data.mileages'));

        // привязываем бренд
        $brand = Brand::find($brandID);
        $brand->mileages()->attach([1,2,3]);

        // делаем повторный запрос
        $response = $this->graphQL($this->getQueryStrOnlyBrand($brandID));
        $this->assertNotEmpty($response->json('data.mileages'));
        $this->assertCount(3, $response->json('data.mileages'));
    }

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
            mileages {
                id
                active
               }
            }'
        );
    }

    public static function getQueryStrOnlyActive(): string
    {
        return  sprintf('{
            mileages (active: true) {
                id
                active
               }
            }'
        );
    }

    public static function getQueryStrOnlyBrand($brandId): string
    {
        return  sprintf('{
            mileages (brandId: %s) {
                id
                active
                brands {
                    id
                }
               }
            }',
        $brandId
        );
    }
}
