<?php

namespace Tests\Feature\Mutations\Admin\Car;

use App\DTO\Media\FileDTO;
use App\Models\Media\File;
use App\Models\User\Car;
use App\Services\Media\UploadService;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\CarBuilder;
use Tests\Traits\Statuses;
use Tests\Traits\UserBuilder;

class CarDetachInsuranceFileTest extends TestCase
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
    public function success()
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

        $response = $this->graphQL($this->getQueryStr($car->id));

        $responseData = $response->json('data.carDetachInsuranceFile');

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('hasInsurance', $responseData);
        $this->assertArrayHasKey('insuranceFile', $responseData);

        $this->assertEquals($responseData['id'], $car->id);
        $this->assertFalse($responseData['hasInsurance']);
        $this->assertNull($responseData['insuranceFile']);

        $car->refresh();
        $this->assertNull($car->insuranceFile);

        app(UploadService::class)->removeAllFileAtModel($car);
    }

    /** @test */
    public function if_not_exist_file()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerms([Permissions::USER_CAR_EDIT, Permissions::USER_CAR_GET])
            ->create();
        $this->loginAsAdmin($admin);

        $user = $this->userBuilder()->create();
        $car = $this->carBuilder()->setUserId($user->id)->create();

        $car->refresh();

        $this->assertNull($car->insuranceFile);

        $response = $this->graphQL($this->getQueryStr($car->id));

        $responseData = $response->json('data.carDetachInsuranceFile');

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('hasInsurance', $responseData);
        $this->assertArrayHasKey('insuranceFile', $responseData);

        $this->assertEquals($responseData['id'], $car->id);
        $this->assertFalse($responseData['hasInsurance']);
        $this->assertNull($responseData['insuranceFile']);

        $car->refresh();
        $this->assertNull($car->insuranceFile);
    }

    public static function getQueryStr(int $id): string
    {
        return sprintf('
            mutation {
                carDetachInsuranceFile(id: "%s") {
                    id
                    hasInsurance
                    insuranceFile {
                        url
                    }
                }
            }',
            $id
        );
    }
}

