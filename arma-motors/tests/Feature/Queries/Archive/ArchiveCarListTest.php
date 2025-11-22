<?php

namespace Tests\Feature\Queries\Archive;

use App\Exceptions\ErrorsCode;
use App\Models\Admin\Admin;
use App\Models\User\Car;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Mutations\Admin\Car\CarRestoreTest;
use Tests\Feature\Mutations\User\User\DeleteCarTest;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\CarBuilder;
use Tests\Traits\UserBuilder;

class ArchiveCarListTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;
    use UserBuilder;
    use CarBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function get_success()
    {
        $admin = $this->adminBuilder()->createRoleWithPerms([
            Permissions::ARCHIVE_CAR_LIST, Permissions::USER_CAR_RESTORE
        ])->create();
        $this->loginAsAdmin($admin);

        $userBuilder = $this->userBuilder();
        $carBuilder = $this->carBuilder();
        // first user
        $user1 = $userBuilder->create();
        $car = $carBuilder->setUserId($user1->id)->create();
        $carBuilder->setUserId($user1->id)->create();
        $carBuilder->setUserId($user1->id)->create();
        // second user
        $user2 = $userBuilder->setPhone('89999999999')->setEmail('some@gmail.com')->create();
        $carBuilder->setUserId($user2->id)->create();
        $carBuilder->setUserId($user2->id)->create();
        $carBuilder->setUserId($user2->id)->create();

        $response = $this->graphQL($this->getQueryStr())
            ->assertOk();

        $responseData = $response->json('data.carsArchive');

        $this->assertArrayHasKey('data', $responseData);
        $this->assertArrayHasKey('paginatorInfo', $responseData);

        $this->assertEmpty($responseData['data']);

        // удаляем авто
        $this->loginAsUser($user1);
        $responseForDelete = $this->graphQL(DeleteCarTest::getQueryStrWithoutComment(DeleteCarTest::data($car->id)));

        // делаем повторный запрос в архив
        $this->loginAsAdmin($admin);
        $secondResponse = $this->graphQL($this->getQueryStr());

        $secondResponseData = $secondResponse->json('data.carsArchive');

        $this->assertNotEmpty($secondResponseData['data']);
        $this->assertEquals(1, $secondResponseData['paginatorInfo']['count']);
        $this->assertEquals($car->id, $secondResponseData['data'][0]['id']);

        // делаем запрос на восстановление
        $responseRestore = $this->graphQL(CarRestoreTest::getQueryStr($car->id));

        // делаем еще раз запрос в архив (ничего не должно быть)
        $thirdResponse = $this->graphQL($this->getQueryStr());
        $thirdResponseData = $thirdResponse->json('data.carsArchive');

        $this->assertEmpty($thirdResponseData['data']);
    }

    /** @test */
    public function not_auth()
    {
        $admin = $this->adminBuilder()->createRoleWithPerms([Permissions::ARCHIVE_CAR_LIST])->create();

        $response = $this->graphQL($this->getQueryStr());

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not auth'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_AUTH, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function not_perm()
    {
        $admin = $this->adminBuilder()->createRoleWithPerms([Permissions::ADMIN_EDIT])->create();
        $this->loginAsAdmin($admin);

        $response = $this->graphQL($this->getQueryStr());

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not perm'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_PERM, $response->json('errors.0.extensions.code'));
    }

    public static function getQueryStr(): string
    {
        return  sprintf('{
            carsArchive {
                data{
                    id
                    number
                    vin
                    year
                    brand {
                        name
                    }
                    model {
                        name
                    }
                }
                paginatorInfo {
                    count
                    currentPage
                    hasMorePages
                    lastPage
                }
               }
            }',
        );
    }
}


