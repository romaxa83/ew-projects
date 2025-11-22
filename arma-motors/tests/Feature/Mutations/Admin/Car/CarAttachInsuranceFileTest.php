<?php

namespace Tests\Feature\Mutations\Admin\Car;

use App\DTO\Media\FileDTO;
use App\Exceptions\ErrorsCode;
use App\Models\Media\File;
use App\Models\User\Car;
use App\Services\Media\UploadService;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Tests\Feature\Queries\Admin\GetOneUserCarTest;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\CarBuilder;
use Tests\Traits\Statuses;
use Tests\Traits\UserBuilder;

class CarAttachInsuranceFileTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;
    use Statuses;
    use CarBuilder;
    use UserBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function success_upload()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerms([Permissions::USER_CAR_EDIT, Permissions::USER_CAR_GET])->create();
        $this->loginAsAdmin($admin);

        $user = $this->userBuilder()->create();
        $car = $this->carBuilder()->setUserId($user->id)->create();

        $car->refresh();

        $this->assertNull($car->insuranceFile);
        $this->assertFalse($car->hasInsurance());

        $response = $this->post('graphql',
            [
                'operations' => '{"query": "mutation ($file: [Upload]) {carAttachInsuranceFile(modelId: ' . $car->id .'file: $file) {id, basename, url, type, hash, mime, url}}"}',
                'map' => '{ "file": ["variables.file"] }',
                'file' => [
                    UploadedFile::fake()->image('file.pdf')->size(500),
                ]
            ], ['content-type' => 'multipart/form-data']
        );

        $responseData = $response->json('data.carAttachInsuranceFile');

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('basename', $responseData);
        $this->assertArrayHasKey('type', $responseData);
        $this->assertArrayHasKey('hash', $responseData);
        $this->assertArrayHasKey('url', $responseData);
        $this->assertArrayHasKey('mime', $responseData);

        $this->assertEquals($responseData['type'], Car::FILE_INSURANCE_TYPE);

        $car->refresh();
        $this->assertNotNull($car->insuranceFile);
        $this->assertEquals($responseData['id'], $car->insuranceFile->id);
        $this->assertTrue($car->hasInsurance());

        // запрашиваем данные по авто
        $responseCar = $this->graphQL(GetOneUserCarTest::getQueryStr($car->id));

        $responseDataCar = $responseCar->json('data.car');

        $this->assertEquals($car->id, $responseDataCar['id']);
        $this->assertTrue($responseDataCar['hasInsurance']);
        $this->assertNotNull($responseDataCar['insuranceFile']);
        $this->assertArrayHasKey('id', $responseDataCar['insuranceFile']);
        $this->assertArrayHasKey('url', $responseDataCar['insuranceFile']);

        app(UploadService::class)->removeAllFileAtModel($car);
    }

    /** @test */
    public function upload_if_exist_file()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerms([Permissions::USER_CAR_EDIT, Permissions::USER_CAR_GET])
            ->create();
        $this->loginAsAdmin($admin);

        $service = app(UploadService::class);

        $user = $this->userBuilder()->create();
        $car = $this->carBuilder()->setUserId($user->id)->create();

        $data = [
            'model' => File::MODEL_CAR,
            'modelId' => $car->id,
            'type' => Car::FILE_INSURANCE_TYPE,
            'file' => [
                UploadedFile::fake()->image('first.jpg', 1980, 1240)->size(500),
            ],
        ];
        $dto = FileDTO::byArgs($data);
        $service->uploadFile($dto);

        $car->refresh();

        $this->assertNotNull($car->insuranceFile);

        $response = $this->post('graphql',
            [
                'operations' => '{"query": "mutation ($file: [Upload]) {carAttachInsuranceFile(modelId: ' . $car->id .'file: $file) {id, basename, url, type, hash, mime, url}}"}',
                'map' => '{ "file": ["variables.file"] }',
                'file' => [
                    UploadedFile::fake()->image('file.pdf')->size(500),
                ]
            ], ['content-type' => 'multipart/form-data']
        );

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('error.car have insurance'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::BAD_REQUEST, $response->json('errors.0.extensions.code'));

        app(UploadService::class)->removeAllFileAtModel($car);
    }
}

