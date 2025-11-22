<?php

namespace Tests\Feature\Queries\Catalog\Calc;

use App\Exceptions\ErrorsCode;
use App\Models\Catalogs\Calc\CalcModel;
use App\Models\Catalogs\Car\Brand;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\Builders\CalcModelBuilder;

class CalcModelListTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;
    use CalcModelBuilder;

    /** @test */
    public function success()
    {
        $admin = $this->adminBuilder()->create();
        $this->loginAsAdmin($admin);

        $builder = $this->calcModelBuilder();
        $builder->create();
        $builder->create();
        $builder->create();
        $builder->create();

        $total = CalcModel::count();

        $response = $this->graphQL($this->getQueryStr());

        $responseData = $response->json('data.calcModels');

        $this->assertNotEmpty($responseData['data']);
        $this->assertEquals($total, $responseData['paginatorInfo']['total']);
        $this->assertArrayHasKey('id', $responseData['data'][0]);
        $this->assertArrayHasKey('brand', $responseData['data'][0]);
        $this->assertArrayHasKey('name', $responseData['data'][0]['brand']);
    }

    /** @test */
    public function order_by_brand()
    {
        $admin = $this->adminBuilder()->create();
        $this->loginAsAdmin($admin);

        $volvo = Brand::query()->where('name', 'volvo')->first();
        $renault = Brand::query()->where('name', 'renault')->first();


        $builder = $this->calcModelBuilder();
        $builder->setBrand($volvo)->create();
        $builder->setBrand($volvo)->create();
        $builder->setBrand($renault)->create();
        $builder->setBrand($renault)->create();

        $response = $this->graphQL($this->getQueryStrOrderByBrand('ASC'));

        $firstId = $response->json('data.calcModels.data.0.id');

        $response = $this->graphQL($this->getQueryStrOrderByBrand('DESC'));

        $this->assertNotEquals($firstId, $response->json('data.calcModels.data.0.id'));
    }

    /** @test */
    public function order_by_model()
    {
        $admin = $this->adminBuilder()->create();
        $this->loginAsAdmin($admin);

        $builder = $this->calcModelBuilder();
        $builder->create();
        $builder->create();
        $builder->create();
        $builder->create();

        $response = $this->graphQL($this->getQueryStrOrderByModel('ASC'));
        $firstId = $response->json('data.calcModels.data.0.id');

        $response = $this->graphQL($this->getQueryStrOrderByModel('DESC'));

        $this->assertNotEquals($firstId, $response->json('data.calcModels.data.0.id'));
    }

    /** @test */
    public function order_by_mileage()
    {
        $admin = $this->adminBuilder()->create();
        $this->loginAsAdmin($admin);

        $builder = $this->calcModelBuilder();
        $builder->setMileageId(1)->create();
        $builder->setMileageId(2)->create();
        $builder->setMileageId(3)->create();
        $builder->setMileageId(4)->create();

        $response = $this->graphQL($this->getQueryStrOrderByMileage('ASC'));
        $firstId = $response->json('data.calcModels.data.0.id');

        $response = $this->graphQL($this->getQueryStrOrderByMileage('DESC'));

        $this->assertNotEquals($firstId, $response->json('data.calcModels.data.0.id'));
    }

    /** @test */
    public function order_by_volume()
    {
        $admin = $this->adminBuilder()->create();
        $this->loginAsAdmin($admin);

        $builder = $this->calcModelBuilder();
        $builder->setVolumeId(1)->create();
        $builder->setVolumeId(2)->create();
        $builder->setVolumeId(3)->create();
        $builder->setVolumeId(4)->create();

        $response = $this->graphQL($this->getQueryStrOrderByVolume('ASC'));
        $firstId = $response->json('data.calcModels.data.0.id');

        $response = $this->graphQL($this->getQueryStrOrderByVolume('DESC'));

        $this->assertNotEquals($firstId, $response->json('data.calcModels.data.0.id'));
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
            calcModels {
                data{
                    id
                    brand {
                        name
                    }
                }
                paginatorInfo {
                    count
                    total
                 }
               }
            }'
        );
    }

    public static function getQueryStrOrderByBrand($type): string
    {
        return  sprintf('{
            calcModels (orderByBrand: "%s") {
                data{
                    id
                    brand {
                        name
                    }
                }
               }
            }',
        $type
        );
    }

    public static function getQueryStrOrderByModel($type): string
    {
        return  sprintf('{
            calcModels (orderByModel: "%s") {
                data{
                    id
                    model {
                        name
                    }
                }
               }
            }',
            $type
        );
    }

    public static function getQueryStrOrderByMileage($type): string
    {
        return  sprintf('{
            calcModels (orderByMileage: "%s") {
                data{
                    id
                    mileage {
                        value
                    }
                }
               }
            }',
            $type
        );
    }

    public static function getQueryStrOrderByVolume($type): string
    {
        return  sprintf('{
            calcModels (orderByEngineVolume: "%s") {
                data{
                    id
                    engineVolume {
                        volume
                    }
                }
               }
            }',
            $type
        );
    }
}

