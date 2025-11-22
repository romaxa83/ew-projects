<?php

namespace Tests\Feature\Mutations\Calc;

use App\Exceptions\ErrorsCode;
use App\Models\Catalogs\Calc\Mileage;
use App\Models\Catalogs\Car\Brand;
use App\Models\Catalogs\Car\DriveUnit;
use App\Models\Catalogs\Car\EngineVolume;
use App\Models\Catalogs\Car\Fuel;
use App\Models\Catalogs\Car\Transmission;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Builders\CalcModelBuilder;
use Tests\Traits\UserBuilder;

class UserCalcTest extends TestCase
{
    use DatabaseTransactions;
    use UserBuilder;
    use CalcModelBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function get_by_renault()
    {
        $user = $this->userBuilder()->create();
        $this->loginAsUser($user);

        $renault = Brand::query()->where('name', 'renault')->first();
        $mileage = Mileage::find(1);
        $volume = EngineVolume::find(1);
        $calcModelBuilder = $this->calcModelBuilder()
            ->setBrand($renault)
            ->setModelId($renault->models[0]->id)
            ->setMileageId($mileage->id)
            ->setVolumeId($volume->id)
        ;

        $obj = $calcModelBuilder->create();

        $data = [
            'brandId' => $renault->id,
            'modelId' => $renault->models[0]->id,
            'engineVolumeId' => $mileage->id,
            'mileageId' => $volume->id,
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStrRenaul($data)])
            ->assertOk();

        $responseData = $response->json('data.userCalc');

        $this->assertArrayHasKey('totalSpares', $responseData);
        $this->assertArrayHasKey('totalSparesDiscount', $responseData);
        $this->assertArrayHasKey('spares', $responseData);
        $this->assertArrayHasKey('totalWorks', $responseData);
        $this->assertArrayHasKey('totalWorksDiscount', $responseData);
        $this->assertArrayHasKey('works', $responseData);
        $this->assertArrayHasKey('allTotal', $responseData);
        $this->assertArrayHasKey('allTotalDiscount', $responseData);

        $this->assertNotNull($responseData['allTotal']);
        $this->assertNotNull($responseData['allTotalDiscount']);
    }

    /** @test */
    public function get_by_renault_not_exist()
    {
        $user = $this->userBuilder()->create();
        $this->loginAsUser($user);

        $renault = Brand::query()->where('name', 'renault')->first();
        $mileage = Mileage::find(1);
        $volume = EngineVolume::find(1);
        $calcModelBuilder = $this->calcModelBuilder()
            ->setBrand($renault)
            ->setModelId($renault->models[0]->id)
            ->setMileageId($mileage->id)
            ->setVolumeId(2)
        ;

        $obj = $calcModelBuilder->create();

        $data = [
            'brandId' => $renault->id,
            'modelId' => $renault->models[0]->id,
            'engineVolumeId' => $mileage->id,
            'mileageId' => $volume->id,
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStrRenaul($data)]);

        $responseData = $response->json('data.userCalc');

        $this->assertArrayHasKey('allTotal', $responseData);
        $this->assertArrayHasKey('allTotalDiscount', $responseData);

        $this->assertNull($responseData['allTotal']);
        $this->assertNull($responseData['allTotalDiscount']);
    }

    /** @test */
    public function get_by_volvo_if_data_for_renault()
    {
        // запрос на калькульцию для вольво, но с полями как для рено

        $user = $this->userBuilder()->create();
        $this->loginAsUser($user);

        $volvo = Brand::query()->where('name', 'volvo')->first();
        $mileage = Mileage::find(1);
        $volume = EngineVolume::find(1);
        $calcModelBuilder = $this->calcModelBuilder()
            ->setBrand($volvo)
            ->setModelId($volvo->models[0]->id)
            ->setMileageId($mileage->id)
            ->setVolumeId($volume->id)
        ;

        $obj = $calcModelBuilder->create();

        $data = [
            'brandId' => $volvo->id,
            'modelId' => $volvo->models[0]->id,
            'engineVolumeId' => $mileage->id,
            'mileageId' => $volume->id,
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStrRenaul($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals('The fuel id field is required.', $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::BAD_REQUEST, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function get_by_volvo_success()
    {
        $user = $this->userBuilder()->create();
        $this->loginAsUser($user);

        $volvo = Brand::query()->where('name', 'volvo')->first();
        $mileage = Mileage::find(1);
        $volume = EngineVolume::find(1);
        $fuel = Fuel::find(1);
        $calcModelBuilder = $this->calcModelBuilder()
            ->setBrand($volvo)
            ->setModelId($volvo->models[0]->id)
            ->setMileageId($mileage->id)
            ->setVolumeId($volume->id)
            ->setFuelId($fuel->id)
        ;

        $obj = $calcModelBuilder->create();

        $data = [
            'brandId' => $volvo->id,
            'modelId' => $volvo->models[0]->id,
            'engineVolumeId' => $mileage->id,
            'mileageId' => $volume->id,
            'fuelId' => $fuel->id,
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStrVolvo($data)]);

        $responseData = $response->json('data.userCalc');

        $this->assertArrayHasKey('allTotal', $responseData);
        $this->assertArrayHasKey('allTotalDiscount', $responseData);

        $this->assertNotNull($responseData['allTotal']);
        $this->assertNotNull($responseData['allTotalDiscount']);
    }

    /** @test */
    public function get_by_volvo_success_empty()
    {
        $user = $this->userBuilder()->create();
        $this->loginAsUser($user);

        $volvo = Brand::query()->where('name', 'volvo')->first();
        $mileage = Mileage::find(1);
        $volume = EngineVolume::find(1);
        $fuel = Fuel::find(1);
        $calcModelBuilder = $this->calcModelBuilder()
            ->setBrand($volvo)
            ->setModelId($volvo->models[0]->id)
            ->setMileageId($mileage->id)
            ->setVolumeId($volume->id)
            ->setFuelId($fuel->id)
        ;

        $obj = $calcModelBuilder->create();

        $data = [
            'brandId' => $volvo->id,
            'modelId' => $volvo->models[0]->id,
            'engineVolumeId' => $mileage->id,
            'mileageId' => $volume->id,
            'fuelId' => 2,
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStrVolvo($data)]);

        $responseData = $response->json('data.userCalc');

        $this->assertArrayHasKey('allTotal', $responseData);
        $this->assertArrayHasKey('allTotalDiscount', $responseData);

        $this->assertNull($responseData['allTotal']);
        $this->assertNull($responseData['allTotalDiscount']);
    }

    /** @test */
    public function get_by_mitsubishi_if_data_for_renault()
    {
        // запрос на калькульцию для вольво, но с полями как для рено

        $user = $this->userBuilder()->create();
        $this->loginAsUser($user);

        $brand = Brand::query()->where('name', 'mitsubishi')->first();
        $mileage = Mileage::find(1);
        $volume = EngineVolume::find(1);
        $calcModelBuilder = $this->calcModelBuilder()
            ->setBrand($brand)
            ->setModelId($brand->models[0]->id)
            ->setMileageId($mileage->id)
            ->setVolumeId($volume->id)
        ;

        $obj = $calcModelBuilder->create();

        $data = [
            'brandId' => $brand->id,
            'modelId' => $brand->models[0]->id,
            'engineVolumeId' => $mileage->id,
            'mileageId' => $volume->id,
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStrRenaul($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals('The drive unit id field is required.', $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::BAD_REQUEST, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function get_by_mitsubishi_success()
    {
        $user = $this->userBuilder()->create();
        $this->loginAsUser($user);

        $brand = Brand::query()->where('name', 'mitsubishi')->first();
        $mileage = Mileage::find(1);
        $volume = EngineVolume::find(1);
        $fuel = Fuel::find(1);
        $unit = DriveUnit::find(1);
        $transmission = Transmission::find(1);
        $calcModelBuilder = $this->calcModelBuilder()
            ->setBrand($brand)
            ->setModelId($brand->models[0]->id)
            ->setMileageId($mileage->id)
            ->setVolumeId($volume->id)
            ->setFuelId($fuel->id)
            ->setDriveUnitId($unit->id)
            ->setTransmissionId($transmission->id)
        ;

        $obj = $calcModelBuilder->create();

        $data = [
            'brandId' => $brand->id,
            'modelId' => $brand->models[0]->id,
            'engineVolumeId' => $mileage->id,
            'mileageId' => $volume->id,
            'fuelId' => $fuel->id,
            'driveUnitId' => $unit->id,
            'transmissionId' => $transmission->id,
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStrMitsubishi($data)]);

        $responseData = $response->json('data.userCalc');

        $this->assertArrayHasKey('allTotal', $responseData);
        $this->assertArrayHasKey('allTotalDiscount', $responseData);

        $this->assertNotNull($responseData['allTotal']);
        $this->assertNotNull($responseData['allTotalDiscount']);
    }

    /** @test */
    public function get_by_mitsubishi_empty()
    {
        $user = $this->userBuilder()->create();
        $this->loginAsUser($user);

        $brand = Brand::query()->where('name', 'mitsubishi')->first();
        $mileage = Mileage::find(1);
        $volume = EngineVolume::find(1);
        $fuel = Fuel::find(1);
        $unit = DriveUnit::find(1);
        $transmission = Transmission::find(1);
        $calcModelBuilder = $this->calcModelBuilder()
            ->setBrand($brand)
            ->setModelId($brand->models[0]->id)
            ->setMileageId($mileage->id)
            ->setVolumeId($volume->id)
            ->setFuelId($fuel->id)
            ->setDriveUnitId($unit->id)
            ->setTransmissionId($transmission->id)
        ;

        $obj = $calcModelBuilder->create();

        $data = [
            'brandId' => $brand->id,
            'modelId' => $brand->models[0]->id,
            'engineVolumeId' => $mileage->id,
            'mileageId' => $volume->id,
            'fuelId' => $fuel->id,
            'driveUnitId' => $unit->id,
            'transmissionId' => 2,
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStrMitsubishi($data)]);

        $responseData = $response->json('data.userCalc');

        $this->assertArrayHasKey('allTotal', $responseData);
        $this->assertArrayHasKey('allTotalDiscount', $responseData);

        $this->assertNull($responseData['allTotal']);
        $this->assertNull($responseData['allTotalDiscount']);
    }

    private function getQueryStrRenaul(array $data): string
    {
        return sprintf('
            mutation {
                userCalc(input:{
                    brandId: "%s"
                    modelId: "%s"
                    engineVolumeId: "%s"
                    mileageId: "%s"

                }) {
                    totalSpares
                    totalSparesDiscount
                    spares {
                        name
                    }
                    totalWorks
                    totalWorksDiscount
                    works {
                        name
                    }
                    allTotal
                    allTotalDiscount
                }
            }',
            $data['brandId'],
            $data['modelId'],
            $data['engineVolumeId'],
            $data['mileageId']
        );
    }

    private function getQueryStrVolvo(array $data): string
    {
        return sprintf('
            mutation {
                userCalc(input:{
                    brandId: "%s"
                    modelId: "%s"
                    engineVolumeId: "%s"
                    mileageId: "%s"
                    fuelId: "%s"

                }) {
                    totalSpares
                    totalSparesDiscount
                    spares {
                        name
                    }
                    totalWorks
                    totalWorksDiscount
                    works {
                        name
                    }
                    allTotal
                    allTotalDiscount
                }
            }',
            $data['brandId'],
            $data['modelId'],
            $data['engineVolumeId'],
            $data['mileageId'],
            $data['fuelId']
        );
    }

    private function getQueryStrMitsubishi(array $data): string
    {
        return sprintf('
            mutation {
                userCalc(input:{
                    brandId: "%s"
                    modelId: "%s"
                    engineVolumeId: "%s"
                    mileageId: "%s"
                    fuelId: "%s"
                    driveUnitId: "%s"
                    transmissionId: "%s"
                }) {
                    totalSpares
                    totalSparesDiscount
                    spares {
                        name
                    }
                    totalWorks
                    totalWorksDiscount
                    works {
                        name
                    }
                    allTotal
                    allTotalDiscount
                }
            }',
            $data['brandId'],
            $data['modelId'],
            $data['engineVolumeId'],
            $data['mileageId'],
            $data['fuelId'],
            $data['driveUnitId'],
            $data['transmissionId']
        );
    }
}

