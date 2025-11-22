<?php

namespace Tests\Feature\Mutations\User\User;

use App\Events\User\SaveCarFromAA;
use App\Events\User\SendCarDataToAA;
use App\Models\AA\AAResponse;
use App\Models\Catalogs\Car\Brand;
use App\Models\Catalogs\Car\Model;
use App\Models\User\Car;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\CarBuilder;
use Tests\Traits\UserBuilder;

class AddCarTest extends TestCase
{
    use DatabaseTransactions;
    use UserBuilder;
    use CarBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function success_not_car_to_aa()
    {
        \Event::fake([
            SendCarDataToAA::class,
            SaveCarFromAA::class,
        ]);

        $user = $this->userBuilder()
            ->setUuid('4e5d19f0-fc22-11eb-8274-4cd98fc26f15')
            ->create();
        $this->loginAsUser($user);
        $user->refresh();

        $brand = Brand::orderBy(\DB::raw('RAND()'))->first();
        $model = Model::orderBy(\DB::raw('RAND()'))->first();

        $data = [
            [
                'brandId' => $brand->id,
                'modelId' => $model->id,
                'number' => 'aa1111aa',
                'vin' => 'aa1111aa',
                'year' => '2012',
            ]
        ];

        $this->assertEmpty($user->cars);
        $this->assertEmpty($user->aaResponses);

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)])
            ->assertOk();

        $responseData = $response->json('data.userAddCars');

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('id', $responseData['cars'][0]);
        $this->assertArrayHasKey('number', $responseData['cars'][0]);
        $this->assertArrayHasKey('vin', $responseData['cars'][0]);
        $this->assertArrayHasKey('year', $responseData['cars'][0]);
        $this->assertArrayHasKey('status', $responseData['cars'][0]);
        $this->assertArrayHasKey('isPersonal', $responseData['cars'][0]);
        $this->assertArrayHasKey('isVerify', $responseData['cars'][0]);
        $this->assertArrayHasKey('isBuy', $responseData['cars'][0]);
        $this->assertArrayHasKey('isAddToApp', $responseData['cars'][0]);
        $this->assertArrayHasKey('createdAt', $responseData['cars'][0]);
        $this->assertArrayHasKey('selected', $responseData['cars'][0]);
        $this->assertArrayHasKey('model', $responseData['cars'][0]);
        $this->assertArrayHasKey('id', $responseData['cars'][0]['model']);
        $this->assertArrayHasKey('name', $responseData['cars'][0]['model']);

        $this->assertArrayHasKey('brand', $responseData['cars'][0]);
        $this->assertArrayHasKey('id', $responseData['cars'][0]['brand']);
        $this->assertArrayHasKey('name', $responseData['cars'][0]['brand']);

        $user->refresh();

        $this->assertNotEmpty($user->aaResponses);
        $this->assertNotEmpty($user->aaResponses[0]->type, AAResponse::TYPE_GET_CAR);

        $this->assertNotEmpty($user->cars);
        $this->assertEquals($user->cars[0]->id, $responseData['cars'][0]['id']);
        $this->assertEquals($user->cars[0]->model->id, $responseData['cars'][0]['model']['id']);
        $this->assertEquals($user->cars[0]->model->id, $model->id);
        $this->assertEquals($user->cars[0]->brand->id, $responseData['cars'][0]['brand']['id']);
        $this->assertEquals($user->cars[0]->brand->id, $brand->id);

        $this->assertTrue($responseData['cars'][0]['isPersonal']);
        $this->assertTrue($responseData['cars'][0]['isAddToApp']);
        $this->assertFalse($responseData['cars'][0]['isVerify']);
        $this->assertFalse($responseData['cars'][0]['isBuy']);
        $this->assertTrue($responseData['cars'][0]['selected']);
        $this->assertEquals($user->cars[0]->status, Car::DRAFT);

        // проверяет запустились ли события
        \Event::assertDispatched(SendCarDataToAA::class);
    }

    /** @test */
    public function add_car_user_not_have_aa()
    {
        \Event::fake([
            SendCarDataToAA::class,
            SaveCarFromAA::class,
        ]);

        $user = $this->userBuilder()
            ->create();
        $this->loginAsUser($user);
        $user->refresh();

        $brand = Brand::orderBy(\DB::raw('RAND()'))->first();
        $model = Model::orderBy(\DB::raw('RAND()'))->first();

        $data = [
            [
                'brandId' => $brand->id,
                'modelId' => $model->id,
                'number' => 'aa1111aa',
                'vin' => 'aa1111aa',
                'year' => '2012',
            ]
        ];

        $this->assertNull($user->uuid);
        $this->assertEmpty($user->cars);
        $this->assertEmpty($user->aaResponses);

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)])
            ->assertOk();

        $responseData = $response->json('data.userAddCars');

        $user->refresh();

        $this->assertEmpty($user->aaResponses);

        $this->assertNotEmpty($user->cars);
        $this->assertEquals($user->cars[0]->id, $responseData['cars'][0]['id']);
        $this->assertEquals($user->cars[0]->model->id, $responseData['cars'][0]['model']['id']);
        $this->assertEquals($user->cars[0]->model->id, $model->id);
        $this->assertEquals($user->cars[0]->brand->id, $responseData['cars'][0]['brand']['id']);
        $this->assertEquals($user->cars[0]->brand->id, $brand->id);

        $this->assertTrue($responseData['cars'][0]['isPersonal']);
        $this->assertTrue($responseData['cars'][0]['isAddToApp']);
        $this->assertFalse($responseData['cars'][0]['isVerify']);
        $this->assertFalse($responseData['cars'][0]['isBuy']);
        $this->assertTrue($responseData['cars'][0]['selected']);
        $this->assertEquals($user->cars[0]->status, Car::DRAFT);

        \Event::assertNotDispatched(SendCarDataToAA::class);
        \Event::assertNotDispatched(SaveCarFromAA::class);
    }

    /** @test */
    public function add_more_cars()
    {
        $user = $this->userBuilder()->create();
        $this->loginAsUser($user);

        $brand = Brand::orderBy(\DB::raw('RAND()'))->first();
        $model = Model::orderBy(\DB::raw('RAND()'))->first();

        $data = [
            [
                'brandId' => $brand->id,
                'modelId' => $model->id,
                'number' => 'aa1111aa',
                'vin' => 'aa1111aa',
                'year' => '2012',
            ],
            [
                'brandId' => $brand->id,
                'modelId' => $model->id,
                'number' => 'aa2222aa',
                'vin' => 'aa2222aa',
                'year' => '2011',
            ]
        ];

        $this->assertEmpty($user->cars);

        $response = $this->postGraphQL(['query' => $this->getQueryStrTwoCar($data)])
            ->assertOk();

        $responseData = $response->json('data.userAddCars');

        $user->refresh();

        $this->assertNotEmpty($user->cars);
        $this->assertCount(2, $user->cars);
        $this->assertEquals($user->cars[0]->id, $responseData['cars'][0]['id']);
        $this->assertEquals($user->cars[0]->vin, $responseData['cars'][0]['vin']);
        $this->assertTrue($user->cars[0]->selected);
        $this->assertEquals($user->cars[1]->id, $responseData['cars'][1]['id']);
        $this->assertEquals($user->cars[1]->vin, $responseData['cars'][1]['vin']);
        $this->assertFalse($user->cars[1]->selected);
    }

    /** @test */
    public function add_if_exist_car()
    {
        $user = $this->userBuilder()->create();
        $this->loginAsUser($user);

        $brand = Brand::orderBy(\DB::raw('RAND()'))->first();
        $model = Model::orderBy(\DB::raw('RAND()'))->first();

        $data = [
            [
                'brandId' => $brand->id,
                'modelId' => $model->id,
                'number' => 'aa1111aa',
                'vin' => 'aa1111aa',
                'year' => '2012',
            ]
        ];

        $this->assertEmpty($user->cars);

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)]);
        $responseData = $response->json('data.userAddCars');

        $user->refresh();

        $this->assertNotEmpty($user->cars);
        $this->assertCount(1, $user->cars);
        $this->assertEquals($user->cars[0]->id, $responseData['cars'][0]['id']);
        $this->assertEquals($user->cars[0]->vin, $responseData['cars'][0]['vin']);
        $this->assertTrue($user->cars[0]->selected);

        $brand2 = Brand::orderBy(\DB::raw('RAND()'))->first();
        $model2 = Model::orderBy(\DB::raw('RAND()'))->first();

        $data2 = [
            [
                'brandId' => $brand2->id,
                'modelId' => $model2->id,
                'number' => 'aa5555aa',
                'vin' => 'AA6666AA',
                'year' => '2012',
            ]
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data2)]);
        $responseData = $response->json('data.userAddCars');

        $user->refresh();

        $this->assertCount(2, $user->cars);
        $this->assertEquals($user->cars[1]->id, $responseData['cars'][1]['id']);
        $this->assertEquals($user->cars[1]->vin, $data2[0]['vin']);
        $this->assertFalse($user->cars[1]->selected);
    }

    /** @test */
    public function exist_car_by_number()
    {
        $user = $this->userBuilder()->create();
        $this->loginAsUser($user);

        $brand = Brand::orderBy(\DB::raw('RAND()'))->first();
        $model = Model::orderBy(\DB::raw('RAND()'))->first();

        $data = [
            [
                'brandId' => $brand->id,
                'modelId' => $model->id,
                'number' => 'AA1111AA',
                'vin' => 'aa1111aa',
                'year' => '2012',
            ]
        ];

        $this->postGraphQL(['query' => $this->getQueryStr($data)])->assertOk();

        // повторный запрос
        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('error.car with number exist', ['number' => $data[0]['number']]), $response->json('errors.0.message'));
    }

    /** @test */
    public function exist_car_by_number_to_archive()
    {
        $user = $this->userBuilder()->create();
        $this->loginAsUser($user);

        $number = 'AA1111AA';
        $carBuilder = $this->carBuilder();
        $car = $carBuilder->setNumber($number)->setUserId($user->id)->softDeleted()->create();

        $data = [
            [
                'brandId' => $carBuilder->getBrandId(),
                'modelId' => $carBuilder->getModelId(),
                'number' => $number,
                'vin' => $carBuilder->getVin(),
                'year' => $carBuilder->getYear(),
            ]
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('error.car exist to archive'), $response->json('errors.0.message'));
    }

    private function getQueryStr(array $data): string
    {
        return sprintf('
            mutation {
                userAddCars(input: [{
                    brandId: "%s"
                    modelId: "%s"
                    number: "%s"
                    vin: "%s"
                    year: "%s"
                    isPersonal: true
                }]) {
                    id
                    cars {
                        id
                        number
                        vin
                        year
                        status
                        isPersonal
                        isVerify
                        isBuy
                        isAddToApp
                        createdAt
                        selected
                        brand {
                            id
                            name
                        }
                        model {
                            id
                            name
                        }
                    }
                }
            }',
            $data[0]['brandId'],
            $data[0]['modelId'],
            $data[0]['number'],
            $data[0]['vin'],
            $data[0]['year'],
        );
    }

    private function getQueryStrTwoCar(array $data): string
    {
        return sprintf('
            mutation {
                userAddCars(input: [
                {
                    brandId: "%s"
                    modelId: "%s"
                    number: "%s"
                    vin: "%s"
                    year: "%s"
                    isPersonal: true
                },
                {
                    brandId: "%s"
                    modelId: "%s"
                    number: "%s"
                    vin: "%s"
                    year: "%s"
                    isPersonal: true
                }
                ]) {
                    id
                    cars {
                        id
                        number
                        vin
                        year
                        status
                        isPersonal
                    }
                }
            }',
            $data[0]['brandId'],
            $data[0]['modelId'],
            $data[0]['number'],
            $data[0]['vin'],
            $data[0]['year'],
            $data[1]['brandId'],
            $data[1]['modelId'],
            $data[1]['number'],
            $data[1]['vin'],
            $data[1]['year'],
        );
    }
}



