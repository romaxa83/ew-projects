<?php

namespace Tests\Feature\Queries\Catalog\Car;

use App\Models\Catalogs\Car\Brand;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Mutations\Catalog\Car\BrandToggleActiveTest;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;

class GetBrandPaginatorTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;

    /** @test */
    public function success()
    {
        $total = Brand::count();

        $response = $this->graphQL($this->getQueryStr());

        $responseData = $response->json('data.brands');

        $this->assertCount($total, $responseData['data']);
        $this->assertArrayHasKey('id', $responseData['data'][0]);
        $this->assertArrayHasKey('uuid', $responseData['data'][0]);
        $this->assertArrayHasKey('active', $responseData['data'][0]);
        $this->assertArrayHasKey('sort', $responseData['data'][0]);
        $this->assertArrayHasKey('isMain', $responseData['data'][0]);
        $this->assertArrayHasKey('name', $responseData['data'][0]);
        $this->assertArrayHasKey('color', $responseData['data'][0]);

        $this->assertArrayHasKey('total', $responseData['paginatorInfo']);
        $this->assertArrayHasKey('count', $responseData['paginatorInfo']);
        $this->assertArrayHasKey('currentPage', $responseData['paginatorInfo']);
        $this->assertArrayHasKey('hasMorePages', $responseData['paginatorInfo']);
        $this->assertArrayHasKey('lastPage', $responseData['paginatorInfo']);

        $this->assertEquals($responseData['paginatorInfo']['total'], $total);
        $this->assertEquals($responseData['paginatorInfo']['count'], $total);
        $this->assertFalse($responseData['paginatorInfo']['hasMorePages']);
    }

    /** @test */
    public function only_main()
    {
        $total = Brand::where('is_main', true)->count();

        $response = $this->graphQL($this->getQueryStrMain());

        $responseData = $response->json('data.brands');

        $this->assertCount($total, $responseData['data']);

        foreach ($responseData['data'] as $item){
            $this->assertTrue($item['isMain']);
        }
    }

    /** @test */
    public function only_active()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CATALOG_BRAND_EDIT)
            ->create();
        $this->loginAsAdmin($admin);

        $total = Brand::count();
        $response = $this->graphQL($this->getQueryStrActive());
        $this->assertEquals($total, $response->json('data.brands.paginatorInfo.total'));

        // запрос на переключение первого элемента
        $this->graphQL(BrandToggleActiveTest::getQueryStr(1));

        $response = $this->graphQL($this->getQueryStrActive());

        $this->assertNotEquals($total, $response->json('data.brands.paginatorInfo.total'));
        $this->assertEquals($total - 1, $response->json('data.brands.paginatorInfo.total'));
    }

    public static function getQueryStr(): string
    {
        return  sprintf('{
            brands {
                data{
                    id
                    uuid
                    isMain
                    active
                    sort
                    name
                    color
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

    public static function getQueryStrMain(): string
    {
        return  sprintf('{
            brands (isMain: true) {
                data{
                    id
                    uuid
                    isMain
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

    public static function getQueryStrActive(): string
    {
        return  sprintf('{
            brands (active: true) {
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
}
