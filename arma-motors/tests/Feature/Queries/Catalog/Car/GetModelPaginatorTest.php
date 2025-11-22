<?php

namespace Tests\Feature\Queries\Catalog\Car;

use App\Models\Catalogs\Car\Brand;
use App\Models\Catalogs\Car\Model;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Mutations\Catalog\Car\ModelToggleActiveTest;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;

class GetModelPaginatorTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;

    /** @test */
    public function success()
    {
        $total = Model::count();

        $response = $this->graphQL($this->getQueryStr());

        $responseData = $response->json('data.models');

        $this->assertCount(10, $responseData['data']);
        $this->assertArrayHasKey('id', $responseData['data'][0]);
        $this->assertArrayHasKey('uuid', $responseData['data'][0]);
        $this->assertArrayHasKey('active', $responseData['data'][0]);
        $this->assertArrayHasKey('sort', $responseData['data'][0]);
        $this->assertArrayHasKey('name', $responseData['data'][0]);
        $this->assertArrayHasKey('calcs', $responseData['data'][0]);

        $this->assertEmpty($responseData['data'][0]['calcs']);

        $this->assertArrayHasKey('total', $responseData['paginatorInfo']);
        $this->assertArrayHasKey('count', $responseData['paginatorInfo']);
        $this->assertArrayHasKey('currentPage', $responseData['paginatorInfo']);
        $this->assertArrayHasKey('hasMorePages', $responseData['paginatorInfo']);
        $this->assertArrayHasKey('lastPage', $responseData['paginatorInfo']);

        $this->assertEquals($responseData['paginatorInfo']['total'], $total);
        $this->assertEquals($responseData['paginatorInfo']['count'], 10);
        $this->assertTrue($responseData['paginatorInfo']['hasMorePages']);
    }

    /** @test */
    public function only_active()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CATALOG_MODEL_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $total = Model::count();
        $response = $this->graphQL($this->getQueryStrActive());
        $this->assertEquals($total, $response->json('data.models.paginatorInfo.total'));

        // запрос на переключение первого элемента
        $this->graphQL(ModelToggleActiveTest::getQueryStr(10));

        $response = $this->graphQL($this->getQueryStrActive());

        $this->assertNotEquals($total, $response->json('data.models.paginatorInfo.total'));
        $this->assertEquals($total - 1, $response->json('data.models.paginatorInfo.total'));
    }

    /** @test */
    public function by_brand()
    {
        $brand = Brand::find(1);
        $total = Model::where('brand_id', $brand->id)->count();

        $response = $this->graphQL($this->getQueryStrByBrand($brand->id));
        $this->assertEquals($total, $response->json('data.models.paginatorInfo.total'));
    }

    /** @test */
    public function for_credit()
    {
        $totalTrue = Model::where('for_credit', true)->count();
        $response = $this->graphQL($this->getQueryStrForCredit(true));

        $this->assertEquals($totalTrue, $response->json('data.models.paginatorInfo.total'));

        $totalFalse = Model::where('for_credit', false)->count();

        $response = $this->graphQL($this->getQueryStrForCredit(false));

        $this->assertEquals($totalFalse, $response->json('data.models.paginatorInfo.total'));
        $this->assertNotEquals($totalFalse, $totalTrue);
    }

    /** @test */
    public function for_calc()
    {
        $totalTrue = Model::where('for_calc', true)->count();
        $response = $this->graphQL($this->getQueryStrForCalc(true));

        $this->assertEquals($totalTrue, $response->json('data.models.paginatorInfo.total'));

        $totalFalse = Model::where('for_calc', false)->count();

        $response = $this->graphQL($this->getQueryStrForCalc(false));

        $this->assertEquals($totalFalse, $response->json('data.models.paginatorInfo.total'));
        $this->assertNotEquals($totalFalse, $totalTrue);
    }

    /** @test */
    public function for_credit_and_brand()
    {
        $brand = Brand::find(2);
        $totalTrue = Model::where('for_credit', true)
            ->where('brand_id', $brand->id)->count();

        $response = $this->graphQL($this->getQueryStrForCreditAndBrand($brand->id,true));

        $this->assertEquals($totalTrue, $response->json('data.models.paginatorInfo.total'));

        $totalFalse = Model::where('for_credit', false)
            ->where('brand_id', $brand->id)->count();

        $response = $this->graphQL($this->getQueryStrForCreditAndBrand($brand->id,false));

        $this->assertEquals($totalFalse, $response->json('data.models.paginatorInfo.total'));
        $this->assertNotEquals($totalFalse, $totalTrue);
    }

    /** @test */
    public function filter_model_name()
    {
        $name = 'Lancer';
        $total = Model::where('name', 'like', $name . '%')->count();

        $response = $this->graphQL($this->getQueryStrFilterModelName($name));
        $this->assertEquals($total, $response->json('data.models.paginatorInfo.total'));

        $response = $this->graphQL($this->getQueryStrFilterModelName('some'));
        $this->assertNotEquals($total, $response->json('data.models.paginatorInfo.total'));
    }

    /** @test */
    public function filter_brand_name()
    {
        $name = 'volvo';
        $total = Model::with('brand')
            ->whereHas('brand', function ($q) use ($name){
                $q->where('name','like', $name . '%');
            })->count();

        $response = $this->graphQL($this->getQueryStrFilterBrandName($name));

        $this->assertEquals($total, $response->json('data.models.paginatorInfo.total'));

        $response = $this->graphQL($this->getQueryStrFilterModelName('some'));

        $this->assertNotEquals($total, $response->json('data.models.paginatorInfo.total'));
    }

    /** @test */
    public function order_by_brand()
    {
        $response = $this->graphQL($this->getQueryStrOrderByBrand('ASC'));
        $firstId = $response->json('data.models.data.0.id');

        $response = $this->graphQL($this->getQueryStrOrderByBrand('DESC'));

        $this->assertNotEquals($firstId, $response->json('data.models.data.0.id'));
    }

    public static function getQueryStr(): string
    {
        return  sprintf('{
            models {
                data{
                    id
                    uuid
                    active
                    sort
                    name
                    calcs {
                        id
                    }
                }
                paginatorInfo {
                    total
                    count
                    currentPage
                    hasMorePages
                    lastPage
                }
               }
            }'
        );
    }

    public static function getQueryStrForCredit(bool $forCredit): string
    {
        $forCredit = $forCredit ? "true" : "false";

        return "{models (forCredit: {$forCredit}) {
                data{
                    id
                    forCredit
                }
                paginatorInfo {
                    total
                }
               }
            }";

    }

    public static function getQueryStrForCreditAndBrand($brandId, bool $forCredit): string
    {
        $forCredit = $forCredit ? "true" : "false";

        return "{models (brandId: {$brandId}, forCredit: {$forCredit}) {
                data{
                    id
                    forCredit
                }
                paginatorInfo {
                    total
                }
               }
            }";

    }

    public static function getQueryStrForCalc(bool $forCalc): string
    {
        $forCalc = $forCalc ? "true" : "false";

        return "{models (forCalc: {$forCalc}) {
                data{
                    id
                    forCredit
                }
                paginatorInfo {
                    total
                }
               }
            }";

    }

    public static function getQueryStrActive(): string
    {
        return  sprintf('{
            models (active: true) {
                data{
                    id
                    active
                }
                paginatorInfo {
                    total
                }
               }
            }'
        );
    }

    public static function getQueryStrByBrand(string $brandId): string
    {
        return  sprintf('{
            models (brandId: "%s") {
                data{
                    id
                    active
                }
                paginatorInfo {
                    total
                }
               }
            }',
        $brandId
        );
    }

    public static function getQueryStrFilterModelName(string $name): string
    {
        return  sprintf('{
            models (modelName: "%s") {
                data{
                    id
                }
                paginatorInfo {
                    total
                }
               }
            }',
        $name
        );
    }

    public static function getQueryStrFilterBrandName(string $name): string
    {
        return  sprintf('{
            models (brandName: "%s") {
                data{
                    id
                }
                paginatorInfo {
                    total
                }
               }
            }',
            $name
        );
    }

    public static function getQueryStrOrderByBrand(string $type): string
    {
        return  sprintf('{
            models (orderByBrand: "%s") {
                data{
                    id
                }
                paginatorInfo {
                    total
                }
               }
            }',
            $type
        );
    }
}
