<?php

namespace Tests\Feature\Mutations\Catalog\Calc;

use App\Exceptions\ErrorsCode;
use App\Models\Catalogs\Calc\Mileage;
use App\Models\Catalogs\Car\Brand;
use App\Models\Catalogs\Car\DriveUnit;
use App\Models\Catalogs\Car\EngineVolume;
use App\Models\Catalogs\Car\Fuel;
use App\Models\Catalogs\Car\Transmission;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\Builders\CalcModelBuilder;

class CalcModelCreateTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;
    use CalcModelBuilder;

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

//        $brand = Brand::query()->where('name', 'volvo')->first();
        $brand = Brand::query()->where('name', 'mitsubishi')->first();


        $model = $brand->models[0];
        $mileage = Mileage::find(1);
        $volume = EngineVolume::find(1);
        $driveUnit = DriveUnit::find(1);
        $transmission = Transmission::find(1);
        $fuel = Fuel::find(1);

        $data = $this->data(
            $brand->id,
            $model->id,
            $mileage->id,
            $volume->id,
            $driveUnit->id,
            $transmission->id,
            $fuel->id,
        );

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)])
            ->assertOk();
//dd($response);
        $responseData = $response->json('data.calcModelCreate');

        $this->assertArrayHasKey('id', $responseData);

        $this->assertArrayHasKey('brand', $responseData);
        $this->assertArrayHasKey('id', $responseData['brand']);
        $this->assertArrayHasKey('name', $responseData['brand']);

        $this->assertArrayHasKey('model', $responseData);
        $this->assertArrayHasKey('id', $responseData['model']);
        $this->assertArrayHasKey('name', $responseData['model']);

        $this->assertArrayHasKey('mileage', $responseData);
        $this->assertArrayHasKey('id', $responseData['mileage']);
        $this->assertArrayHasKey('value', $responseData['mileage']);

        $this->assertArrayHasKey('engineVolume', $responseData);
        $this->assertArrayHasKey('id', $responseData['engineVolume']);
        $this->assertArrayHasKey('volume', $responseData['engineVolume']);

        $this->assertArrayHasKey('driveUnit', $responseData);
        $this->assertArrayHasKey('id', $responseData['driveUnit']);
        $this->assertArrayHasKey('name', $responseData['driveUnit']);

        $this->assertArrayHasKey('transmission', $responseData);
        $this->assertArrayHasKey('id', $responseData['transmission']);
        $this->assertArrayHasKey('current', $responseData['transmission']);
        $this->assertArrayHasKey('name', $responseData['transmission']['current']);

        $this->assertArrayHasKey('fuel', $responseData);
        $this->assertArrayHasKey('id', $responseData['fuel']);
        $this->assertArrayHasKey('current', $responseData['fuel']);
        $this->assertArrayHasKey('name', $responseData['fuel']['current']);

//        $this->assertArrayHasKey('works', $responseData);
//        $this->assertArrayHasKey('id', $responseData['works'][0]);
//        $this->assertArrayHasKey('current', $responseData['works'][0]);
//        $this->assertArrayHasKey('name', $responseData['works'][0]['current']);
//        $this->assertArrayHasKey('pivot', $responseData['works'][0]);
//        $this->assertArrayHasKey('minutes', $responseData['works'][0]['pivot']);

//        $this->assertArrayHasKey('spares', $responseData);
//        $this->assertArrayHasKey('id', $responseData['spares'][0]);
//        $this->assertArrayHasKey('name', $responseData['spares'][0]);
//        $this->assertArrayHasKey('pivot', $responseData['spares'][0]);
//        $this->assertArrayHasKey('qty', $responseData['spares'][0]['pivot']);

        $this->assertEquals($responseData['brand']['name'], $brand->name);
        $this->assertEquals($responseData['model']['name'], $model->name);
        $this->assertEquals($responseData['mileage']['value'], $mileage->value);
        $this->assertEquals($responseData['engineVolume']['volume'], $volume->volume);
        $this->assertEquals($responseData['driveUnit']['name'], $driveUnit->name);
        $this->assertEquals($responseData['transmission']['current']['name'], $transmission->current->name);
        $this->assertEquals($responseData['fuel']['current']['name'], $fuel->current->name);

//        $this->assertCount(2, $responseData['works']);
//        foreach ($responseData['works'] as $key => $work){
//            $this->assertEquals($work['id'], $data['works'][$key]['id']);
//            $this->assertEquals($work['pivot']['minutes'], $data['works'][$key]['minutes']);
//        }
//
//        $this->assertCount(2, $responseData['spares']);
//        foreach ($responseData['spares'] as $key => $spares){
//            $this->assertEquals($spares['id'], $data['spares'][$key]['id']);
//            $this->assertEquals($spares['pivot']['qty'], $data['spares'][$key]['qty']);
//        }
    }

    /** @test */
    public function create_renault()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CALC_CATALOG_CREATE)
            ->create();
        $this->loginAsAdmin($admin);

        $brand = Brand::query()->where('name', 'renault')->first();
        $model = $brand->models[0];
        $mileage = Mileage::find(1);
        $volume = EngineVolume::find(1);

        $data = [
            'brandId' => $brand->id,
            'modelId' => $model->id,
            'engineVolumeId' => $volume->id,
            'mileageId' => $mileage->id,
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStrForRenault($data)])
            ->assertOk();

        $responseData = $response->json('data.calcModelCreate');

        $this->assertArrayHasKey('id', $responseData);

        $this->assertArrayHasKey('brand', $responseData);
        $this->assertArrayHasKey('id', $responseData['brand']);
        $this->assertArrayHasKey('name', $responseData['brand']);

        $this->assertArrayHasKey('model', $responseData);
        $this->assertArrayHasKey('id', $responseData['model']);
        $this->assertArrayHasKey('name', $responseData['model']);

        $this->assertArrayHasKey('mileage', $responseData);
        $this->assertArrayHasKey('id', $responseData['mileage']);
        $this->assertArrayHasKey('value', $responseData['mileage']);

        $this->assertArrayHasKey('engineVolume', $responseData);
        $this->assertArrayHasKey('id', $responseData['engineVolume']);
        $this->assertArrayHasKey('volume', $responseData['engineVolume']);

        $this->assertArrayHasKey('driveUnit', $responseData);
        $this->assertNull($responseData['driveUnit']);

        $this->assertArrayHasKey('transmission', $responseData);
        $this->assertNull($responseData['transmission']);

        $this->assertArrayHasKey('fuel', $responseData);
        $this->assertNull($responseData['fuel']);


        $this->assertArrayHasKey('works', $responseData);
        $this->assertEmpty($responseData['works']);

        $this->assertArrayHasKey('spares', $responseData);
        $this->assertEmpty($responseData['spares']);
    }

    /** @test */
    public function create_fail_mitsubishi()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CALC_CATALOG_CREATE)
            ->create();
        $this->loginAsAdmin($admin);

        $brand = Brand::query()->where('name', 'mitsubishi')->first();
        $model = $brand->models[0];
        $mileage = Mileage::find(1);
        $volume = EngineVolume::find(1);

        $data = [
            'brandId' => $brand->id,
            'modelId' => $model->id,
            'engineVolumeId' => $volume->id,
            'mileageId' => $mileage->id,
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStrForRenault($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals('The drive unit id field is required.', $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::BAD_REQUEST, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function volvo_not_field_engine()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CALC_CATALOG_CREATE)
            ->create();
        $this->loginAsAdmin($admin);

        $brand = Brand::query()->where('name', 'volvo')->first();
        $model = $brand->models[0];
        $mileage = Mileage::find(1);
        $volume = EngineVolume::find(1);

        $data = [
            'brandId' => $brand->id,
            'modelId' => $model->id,
            'mileageId' => $mileage->id,
            'volumeId' => $volume->id,
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStrVolvoNoAllRequired($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals('The engine volume id field is required.', $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::BAD_REQUEST, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function not_auth()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::CALC_CATALOG_CREATE)
            ->create();

        $response = $this->postGraphQL(['query' => $this->getQueryStr($this->data(1,18,1,1,1,1, 1))]);

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

        $response = $this->postGraphQL(['query' => $this->getQueryStr($this->data(1,18,1,1,1,1, 1))]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not perm'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_PERM, $response->json('errors.0.extensions.code'));
    }

    public static function data(
        $brandId,
        $modelId,
        $mileageId,
        $engineVolumeId,
        $driveUnitId,
        $transmissionId,
        $fuelId,
    )
    {
        return [
            'brandId' => $brandId,
            'modelId' => $modelId,
            'mileageId' => $mileageId,
            'engineVolumeId' => $engineVolumeId,
            'driveUnitId' => $driveUnitId,
            'transmissionId' => $transmissionId,
            'fuelId' => $fuelId,
            'works' => [
                [
                    'id' => 1,
                    'minutes' => 10
                ],
                [
                    'id' => 2,
                    'minutes' => 30.7
                ]
            ],
            'spares' => [
                [
                    'id' => 1,
                    'qty' => 10
                ],
                [
                    'id' => 2,
                    'qty' => 3
                ]
            ],
        ];
    }

    public static function getQueryStr(array $data): string
    {
        return sprintf('
            mutation {
                calcModelCreate(input:{
                    modelId: %d,
                    brandId: %d,
                    mileageId: %d,
                    engineVolumeId: %d,
                    driveUnitId: %d,
                    transmissionId: %d,
                    fuelId: %d,
                    works: [
                        {id: %d, minutes: %f}
                        {id: %d, minutes: %f}
                    ]
                    spares: [
                        {id: %d, qty: %d}
                        {id: %d, qty: %d}
                    ]
                }) {
                    id
                    brand {
                        id
                        name
                    }
                    model {
                        id
                        name
                    }
                    mileage {
                        id
                        value
                    }
                    engineVolume {
                        id
                        volume
                    }
                    driveUnit {
                        id
                        name
                    }
                    transmission {
                        id
                        current {
                            name
                        }
                    }
                    fuel {
                        id
                        current {
                            name
                        }
                    }
                    works {
                        id
                        current {
                            name
                        }
                        pivot {
                            minutes
                        }
                    }
                    spares {
                        id
                        name
                        pivot {
                            qty
                        }
                    }
                }
            }',
            $data['modelId'],
            $data['brandId'],
            $data['mileageId'],
            $data['engineVolumeId'],
            $data['driveUnitId'],
            $data['transmissionId'],
            $data['fuelId'],
            $data['works'][0]['id'],
            $data['works'][0]['minutes'],
            $data['works'][1]['id'],
            $data['works'][1]['minutes'],
            $data['spares'][0]['id'],
            $data['spares'][0]['qty'],
            $data['spares'][1]['id'],
            $data['spares'][1]['qty'],
        );
    }

    public static function getQueryStrForRenault(array $data): string
    {
        return sprintf('
            mutation {
                calcModelCreate(input:{
                    modelId: %d,
                    brandId: %d,
                    mileageId: %d,
                    engineVolumeId: %d,
                }) {
                    id
                    brand {
                        id
                        name
                    }
                    model {
                        id
                        name
                    }
                    mileage {
                        id
                        value
                    }
                    engineVolume {
                        id
                        volume
                    }
                    driveUnit {
                        id
                        name
                    }
                    transmission {
                        id
                        current {
                            name
                        }
                    }
                    fuel {
                        id
                        current {
                            name
                        }
                    }
                    works {
                        id
                        current {
                            name
                        }
                        pivot {
                            minutes
                        }
                    }
                    spares {
                        id
                        name
                        pivot {
                            qty
                        }
                    }
                }
            }',
            $data['modelId'],
            $data['brandId'],
            $data['mileageId'],
            $data['engineVolumeId'],
        );
    }

    public static function getQueryStrVolvoNoAllRequired(array $data): string
    {
        return sprintf('
            mutation {
                calcModelCreate(input:{
                    modelId: %d,
                    brandId: %d,
                    mileageId: %d,
                }) {
                    id
                }
            }',
            $data['modelId'],
            $data['brandId'],
            $data['mileageId'],
        );
    }
}
