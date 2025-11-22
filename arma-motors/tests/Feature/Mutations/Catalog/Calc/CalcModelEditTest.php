<?php
//
//namespace Tests\Feature\Mutations\Catalog\Calc;
//
//use App\Exceptions\ErrorsCode;
//use App\Models\Catalogs\Calc\Mileage;
//use App\Models\Catalogs\Car\Brand;
//use App\Models\Catalogs\Car\DriveUnit;
//use App\Models\Catalogs\Car\EngineVolume;
//use App\Models\Catalogs\Car\Fuel;
//use App\Models\Catalogs\Car\Transmission;
//use App\Types\Permissions;
//use Illuminate\Foundation\Testing\DatabaseTransactions;
//use Tests\TestCase;
//use Tests\Traits\AdminBuilder;
//use Tests\Traits\Builders\CalcModelBuilder;
//
//class CalcModelEditTest extends TestCase
//{
//    use DatabaseTransactions;
//    use AdminBuilder;
//    use CalcModelBuilder;
//
//    public function setUp(): void
//    {
//        parent::setUp();
//        $this->passportInit();
//    }
//
//    /** @test */
//    public function success_volvo()
//    {
//        $admin = $this->adminBuilder()
//            ->createRoleWithPerms([Permissions::CALC_CATALOG_EDIT, Permissions::CALC_CATALOG_CREATE])
//            ->create();
//        $this->loginAsAdmin($admin);
//
//        $volvo = Brand::query()->where('name', 'volvo')->first();
//        $renault = Brand::query()->where('name', 'renault')->first();
//
//        $calcModelBuilder = $this->calcModelBuilder()->setBrand($volvo);
//        $obj = $calcModelBuilder->create();
//
//        $obj->refresh();
//
//        $this->assertTrue($obj->brand->isVolvo());
//
//        $model = $renault->models[0];
//        $mileage = Mileage::find(2);
//        $volume = EngineVolume::find(2);
//        $driveUnit = DriveUnit::find(2);
//        $transmission = Transmission::find(2);
//        $fuel = Fuel::find(2);
//
//        $this->assertNotEquals($obj->model_id, $model->id);
//        $this->assertNotEquals($obj->mileage_id, $mileage->id);
//        $this->assertNotEquals($obj->engine_volume_id, $volume->id);
//        $this->assertNotEquals($obj->fuel_id, $fuel->id);
//
//        $this->assertNull($obj->drive_unit_id);
//        $this->assertNull($obj->transmission_id);
//
//        $data = [
//            'id' => $obj->id,
//            'brandId' => $renault->id,
//            'modelId' => $model->id,
//            'mileageId' => $mileage->id,
//            'engineVolumeId' => $volume->id,
//            'fuelId' => $fuel->id,
//        ];
//
//
//        $responseEdit = $this->postGraphQL(['query' => $this->getQueryStrVolvo($data)]);
//
//        dd($responseEdit);
//
//        $responseEditData = $responseEdit->json('data.calcModelEdit');
//
//        $this->assertEquals($data['brandId'], $responseEditData['brand']['id']);
//        $this->assertEquals($data['modelId'], $responseEditData['model']['id']);
//        $this->assertEquals($data['mileageId'], $responseEditData['mileage']['id']);
//        $this->assertEquals($data['engineVolumeId'], $responseEditData['engineVolume']['id']);
//        $this->assertEquals($data['driveUnitId'], $responseEditData['driveUnit']['id']);
//        $this->assertEquals($data['transmissionId'], $responseEditData['transmission']['id']);
//        $this->assertEquals($data['fuelId'], $responseEditData['fuel']['id']);
//
//        $this->assertEquals(count($data['spares']), count($responseEditData['spares']));
////        $this->assertEquals(count($data['works']), count($responseEditData['works']));
//
//        $obj->refresh();
//        $this->assertEquals($obj->brand_id, $brand->id);
//        $this->assertEquals($obj->model_id, $model->id);
//        $this->assertEquals($obj->mileage_id, $mileage->id);
//        $this->assertEquals($obj->engine_volume_id, $volume->id);
//        $this->assertEquals($obj->drive_unit_id, $driveUnit->id);
//        $this->assertEquals($obj->transmission_id, $transmission->id);
//        $this->assertEquals($obj->fuel_id, $fuel->id);
//    }
//
//    /** @test */
//    public function only_required()
//    {
//        $admin = $this->adminBuilder()
//            ->createRoleWithPerms([Permissions::CALC_CATALOG_EDIT, Permissions::CALC_CATALOG_CREATE])
//            ->create();
//        $this->loginAsAdmin($admin);
//
//        $calcModelBuilder = $this->calcModelBuilder();
//        $obj = $calcModelBuilder->create();
//
//        $brand = Brand::find(3);
//        $model = $brand->models[0];
//
//        $this->assertNotEquals($obj->brand_id, $brand->id);
//        $this->assertNotEquals($obj->model_id, $model->id);
//        $this->assertNotNull($obj->mileage_id);
//        $this->assertNotNull($obj->engine_volume_id);
//        $this->assertNotNull($obj->drive_unit_id);
//        $this->assertNotNull($obj->transmission_id);
//        $this->assertNotNull($obj->fuel_id);
//        $this->assertNotEmpty($obj->works);
//        $this->assertNotEmpty($obj->spares);
//
//        $data = [
//            'id' => $obj->id,
//            'modelId' => $model->id,
//            'brandId' => $brand->id,
//        ];
//        $responseEdit = $this->postGraphQL(['query' => $this->getQueryStrOnlyRequiredField($data)]);
//
//        $responseEditData = $responseEdit->json('data.calcModelEdit');
//
//        $this->assertEquals($data['brandId'], $responseEditData['brand']['id']);
//        $this->assertEquals($data['modelId'], $responseEditData['model']['id']);
//        $this->assertNull($responseEditData['mileage']);
//        $this->assertNull($responseEditData['engineVolume']);
//        $this->assertNull($responseEditData['driveUnit']);
//        $this->assertNull($responseEditData['transmission']);
//        $this->assertNull($responseEditData['fuel']);
//        $this->assertEmpty($responseEditData['works']);
//        $this->assertEmpty($responseEditData['spares']);
//
//        $obj->refresh();
//        $this->assertEquals($obj->brand_id, $brand->id);
//        $this->assertEquals($obj->model_id, $model->id);
//        $this->assertNull($obj->mileage_id);
//        $this->assertNull($obj->engine_volume_id);
//        $this->assertNull($obj->drive_unit_id);
//        $this->assertNull($obj->transmission_id);
//        $this->assertNull($obj->fuel_id);
//        $this->assertEmpty($obj->works);
//        $this->assertEmpty($obj->spares);
//    }
//
//    /** @test */
//    public function not_found()
//    {
//        $admin = $this->adminBuilder()
//            ->createRoleWithPerms([Permissions::CALC_CATALOG_EDIT])
//            ->create();
//        $this->loginAsAdmin($admin);
//
//        $calcModelBuilder = $this->calcModelBuilder();
//        $model = $calcModelBuilder->create();
//
//        $data = [
//            'id' => 999,
//            'modelId' => 10,
//            'brandId' => 1,
//        ];
//
//        $response = $this->postGraphQL(['query' => $this->getQueryStrOnlyRequiredField($data)]);
//
//        $this->assertArrayHasKey('errors', $response->json());
//        $this->assertEquals(__('error.not found model'), $response->json('errors.0.message'));
//        $this->assertEquals(ErrorsCode::NOT_FOUND, $response->json('errors.0.extensions.code'));
//    }
//
//    /** @test */
//    public function not_auth()
//    {
//        $admin = $this->adminBuilder()
//            ->createRoleWithPerms([Permissions::CALC_CATALOG_EDIT])
//            ->create();
//
//
//        $calcModelBuilder = $this->calcModelBuilder();
//        $model = $calcModelBuilder->create();
//
//        $data = [
//            'id' => $model->id,
//            'modelId' => 10,
//            'brandId' => 1,
//        ];
//
//        $response = $this->postGraphQL(['query' => $this->getQueryStrOnlyRequiredField($data)]);
//
//        $this->assertArrayHasKey('errors', $response->json());
//        $this->assertEquals(__('auth.not auth'), $response->json('errors.0.message'));
//        $this->assertEquals(ErrorsCode::NOT_AUTH, $response->json('errors.0.extensions.code'));
//    }
//
//    /** @test */
//    public function not_perm()
//    {
//        $admin = $this->adminBuilder()
//            ->createRoleWithPerms([Permissions::CALC_CATALOG_CREATE])
//            ->create();
//        $this->loginAsAdmin($admin);
//
//        $calcModelBuilder = $this->calcModelBuilder();
//        $model = $calcModelBuilder->create();
//
//        $data = [
//            'id' => $model->id,
//            'modelId' => 10,
//            'brandId' => 1,
//        ];
//
//        $response = $this->postGraphQL(['query' => $this->getQueryStrOnlyRequiredField($data)]);
//
//        $this->assertArrayHasKey('errors', $response->json());
//        $this->assertEquals(__('auth.not perm'), $response->json('errors.0.message'));
//        $this->assertEquals(ErrorsCode::NOT_PERM, $response->json('errors.0.extensions.code'));
//    }
//
//    public static function data(
//        $brandId,
//        $modelId,
//        $mileageId,
//        $engineVolumeId,
//        $driveUnitId,
//        $transmissionId,
//        $fuelId
//    )
//    {
//        return [
//            'brandId' => $brandId,
//            'modelId' => $modelId,
//            'mileageId' => $mileageId,
//            'engineVolumeId' => $engineVolumeId,
//            'driveUnitId' => $driveUnitId,
//            'transmissionId' => $transmissionId,
//            'fuelId' => $fuelId,
//            'works' => [
//                [
//                    'id' => 1,
//                    'minutes' => 120
//                ],
//            ],
//            'spares' => [
//                [
//                    'id' => 1,
//                    'qty' => 11
//                ]
//            ],
//        ];
//    }
//
//    public static function getQueryStrVolvo(array $data): string
//    {
//        return sprintf('
//            mutation {
//                calcModelEdit(input:{
//                    id: %d,
//                    modelId: %d,
//                    brandId: %d,
//                    mileageId: %d,
//                    engineVolumeId: %d,
//                    fuelId: %d,
//                }) {
//                    id
//                    brand {
//                        id
//                        name
//                    }
//                    model {
//                        id
//                        name
//                    }
//                    mileage {
//                        id
//                        value
//                    }
//                    engineVolume {
//                        id
//                        volume
//                    }
//                    driveUnit {
//                        id
//                        name
//                    }
//                    transmission {
//                        id
//                        current {
//                            name
//                        }
//                    }
//                    fuel {
//                        id
//                        current {
//                            name
//                        }
//                    }
//                    works {
//                        id
//                        current {
//                            name
//                        }
//                        pivot {
//                            minutes
//                        }
//                    }
//                    spares {
//                        id
//                        name
//                        pivot {
//                            qty
//                        }
//                    }
//                }
//            }',
//            $data['id'],
//            $data['modelId'],
//            $data['brandId'],
//            $data['mileageId'],
//            $data['engineVolumeId'],
//            $data['fuelId'],
//        );
//    }
//
//    public static function getQueryStr(array $data): string
//    {
//        return sprintf('
//            mutation {
//                calcModelEdit(input:{
//                    id: %d,
//                    modelId: %d,
//                    brandId: %d,
//                    mileageId: %d,
//                    engineVolumeId: %d,
//                    driveUnitId: %d,
//                    transmissionId: %d,
//                    fuelId: %d,
//                    works: [
//                        {id: %d, minutes: %d}
//                    ]
//                    spares: [
//                        {id: %d, qty: %d}
//                    ]
//                }) {
//                    id
//                    brand {
//                        id
//                        name
//                    }
//                    model {
//                        id
//                        name
//                    }
//                    mileage {
//                        id
//                        value
//                    }
//                    engineVolume {
//                        id
//                        volume
//                    }
//                    driveUnit {
//                        id
//                        name
//                    }
//                    transmission {
//                        id
//                        current {
//                            name
//                        }
//                    }
//                    fuel {
//                        id
//                        current {
//                            name
//                        }
//                    }
//                    works {
//                        id
//                        current {
//                            name
//                        }
//                        pivot {
//                            minutes
//                        }
//                    }
//                    spares {
//                        id
//                        name
//                        pivot {
//                            qty
//                        }
//                    }
//                }
//            }',
//            $data['id'],
//            $data['modelId'],
//            $data['brandId'],
//            $data['mileageId'],
//            $data['engineVolumeId'],
//            $data['driveUnitId'],
//            $data['transmissionId'],
//            $data['fuelId'],
//            $data['works'][0]['id'],
//            $data['works'][0]['minutes'],
//            $data['spares'][0]['id'],
//            $data['spares'][0]['qty'],
//        );
//    }
//
//
//
//    public static function getQueryStrOnlyRequiredField(array $data): string
//    {
//        return sprintf('
//            mutation {
//                calcModelEdit(input:{
//                    id: %d,
//                    modelId: %d,
//                    brandId: %d,
//                }) {
//                    id
//                    brand {
//                        id
//                        name
//                    }
//                    model {
//                        id
//                        name
//                    }
//                    mileage {
//                        id
//                        value
//                    }
//                    engineVolume {
//                        id
//                        volume
//                    }
//                    driveUnit {
//                        id
//                        name
//                    }
//                    transmission {
//                        id
//                        current {
//                            name
//                        }
//                    }
//                    fuel {
//                        id
//                        current {
//                            name
//                        }
//                    }
//                    works {
//                        id
//                        current {
//                            name
//                        }
//                        pivot {
//                            minutes
//                        }
//                    }
//                    spares {
//                        id
//                        name
//                        pivot {
//                            qty
//                        }
//                    }
//                }
//            }',
//            $data['id'],
//            $data['modelId'],
//            $data['brandId']
//        );
//    }
//}
//
