<?php

namespace Tests\Feature\Mutations\Media;

use App\DTO\Media\FileDTO;
use App\Models\Media\File;
use App\Models\User\Car;
use App\Services\Media\UploadService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\CarBuilder;
use Tests\Traits\UserBuilder;

class RemoveFileTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;
    use UserBuilder;
    use CarBuilder;

    /** @test */
    public function remove_insurance_file_from_car()
    {
        $service = app(UploadService::class);

        $user = $this->userBuilder()->create();
        $car = $this->carBuilder()->setUserId($user->id)->create();

        $this->assertNull($car->insuranceFile);

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

        $removeIds = [
            $car->insuranceFile->id,
        ];

        $response = $this->graphQL($this->getQueryStr($removeIds))
            ->assertOk();

        $responseData = $response->json('data.filesDelete');

        $this->assertArrayHasKey('message', $responseData);
        $this->assertArrayHasKey('status', $responseData);

        $this->assertTrue($responseData['status']);
        $this->assertEquals($responseData['message'], __('message.files remove success'));

        $car->refresh();

        $this->assertNull($car->insuranceFile);
    }

    public static function getQueryStr(array $data): string
    {
        return sprintf('
            mutation {
                filesDelete(ids: [%s]) {
                    status
                    message
                }
            }',
            $data[0]
        );
    }
}
