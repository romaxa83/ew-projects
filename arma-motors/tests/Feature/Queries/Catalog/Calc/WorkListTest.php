<?php

namespace Tests\Feature\Queries\Catalog\Calc;

use App\Exceptions\ErrorsCode;
use App\Models\Catalogs\Calc\Work;
use App\Models\Catalogs\Car\Brand;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Mutations\Catalog\Calc\WorkToggleActiveTest;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\Builders\CalcCatalogBuilder;

class WorkListTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;
    use CalcCatalogBuilder;

    /** @test */
    public function success()
    {
        $admin = $this->adminBuilder()->create();
        $this->loginAsAdmin($admin);

        $calcBuilder = $this->calcCatalogBuilder();
        $total = 5;
        $calcBuilder->createWork($total);

        $response = $this->graphQL($this->getQueryStr());

        $responseData = $response->json('data.works');

        $this->assertNotEmpty($responseData);
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

        $calcBuilder = $this->calcCatalogBuilder();
        $total = 5;
        $calcBuilder->createWork($total);
        $work = $calcBuilder->createWork();

        $response = $this->graphQL($this->getQueryStrOnlyActive());

        $count = count($response->json('data.works'));

        // запрос на переключение первого элемента
        $this->graphQL(WorkToggleActiveTest::getQueryStr($work->id));

        $newResponse = $this->graphQL($this->getQueryStrOnlyActive());

        $this->assertNotEquals($count, count($newResponse->json('data.works')));
        $this->assertEquals($count -1 , count($newResponse->json('data.works')));
    }

    /** @test */
    public function get_by_brand()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CALC_CATALOG_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $calcBuilder = $this->calcCatalogBuilder();
        $total = 5;
        $calcBuilder->createWork($total);

        $brandID = 1;

        // нет привязанных брендов
        $response = $this->graphQL($this->getQueryStrOnlyBrand($brandID));
        $this->assertEmpty($response->json('data.works'));

        // привязываем бренд
        $brand = Brand::find($brandID);
        $work = Work::query()->orderBy(\DB::raw('RAND()'))->first();
        $brand->works()->attach([$work->id]);

        // делаем повторный запрос
        $response = $this->graphQL($this->getQueryStrOnlyBrand($brandID));
        $this->assertNotEmpty($response->json('data.works'));
        $this->assertCount(1, $response->json('data.works'));
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
            works {
                id
                active
               }
            }'
        );
    }

    public static function getQueryStrOnlyActive(): string
    {
        return  sprintf('{
            works (active: true) {
                id
                active
               }
            }'
        );
    }

    public static function getQueryStrOnlyBrand($brandId): string
    {
        return  sprintf('{
            works (brandId: %s) {
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
