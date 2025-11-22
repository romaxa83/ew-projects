<?php

namespace Tests\Feature\Queries\Admin;

use App\DTO\History\HistoryCarDto;
use App\Exceptions\ErrorsCode;
use App\Models\User\User;
use App\Services\History\CarHistoryService;
use App\Types\Permissions;
use Faker\Generator as Faker;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\Builders\RecommendationBuilder;
use Tests\Traits\CarBuilder;
use Tests\Traits\HistoryTestData;

class GetOneUserCarTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;
    use CarBuilder;
    use RecommendationBuilder;
    use HistoryTestData;

    protected $faker;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();

        $this->faker = resolve(Faker::class);
    }

    /** @test */
    public function success()
    {
        $user = User::factory()->create();

        $builder = $this->adminBuilder();
        $carBuilder = $this->carBuilder();
        $car = $carBuilder->setUserId($user->id)->create();

        $admin = $builder->createRoleWithPerms([Permissions::USER_CAR_GET])->create();
        $this->loginAsAdmin($admin);

        $response = $this->graphQL($this->getQueryStr($car->id));

        $responseData = $response->json('data.car');

        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('number', $responseData);
        $this->assertArrayHasKey('vin', $responseData);
        $this->assertArrayHasKey('brand', $responseData);
        $this->assertArrayHasKey('name', $responseData['brand']);
        $this->assertArrayHasKey('model', $responseData);
        $this->assertArrayHasKey('name', $responseData['model']);
        $this->assertArrayHasKey('user', $responseData);
        $this->assertArrayHasKey('name', $responseData['user']);
        $this->assertArrayHasKey('deleteReason', $responseData);
        $this->assertArrayHasKey('deleteComment', $responseData);
        $this->assertArrayHasKey('hasInsurance', $responseData);
        $this->assertArrayHasKey('insuranceFile', $responseData);

        $this->assertNull($responseData['deleteReason']);
        $this->assertNull($responseData['deleteComment']);
        $this->assertNull($responseData['insuranceFile']);

        $this->assertFalse($responseData['hasInsurance']);

        $this->assertEquals($responseData['id'], $car->id);
        $this->assertEquals($responseData['user']['name'], $user->name);
    }

    /** @test */
    public function success_with_recommendations()
    {
        $user = User::factory()->create();

        $builder = $this->adminBuilder();
        $carBuilder = $this->carBuilder();
        $car = $carBuilder->setUuid("37a0c311-01a9-11ec-8274-4cd98fc26f15")
            ->setUserId($user->id)->create();

        $this->recommendationBuilder()->setCarUuid($car->uuid)->create();
        $this->recommendationBuilder()->setCarUuid($car->uuid)->create();

        $admin = $builder->createRoleWithPerms([Permissions::USER_CAR_GET])->create();
        $this->loginAsAdmin($admin);

        $response = $this->graphQL($this->getQueryStrWithRec($car->id));

        $responseData = $response->json('data.car');

        $this->assertArrayHasKey('recommendations', $responseData);
        $this->assertCount(2, $responseData['recommendations']);
    }

    /** @test */
    public function success_with_history()
    {
        $user = User::factory()->create();

        $builder = $this->adminBuilder();
        $carUuid = "9ee4670f-0016-11ec-8274-4cd98fc26f15";
        $carBuilder = $this->carBuilder();
        $car = $carBuilder->setUuid($carUuid)
            ->setUserId($user->id)->create();

        $data = $this->data($this->faker);
        $data['id'] = $carUuid;

        // создаем запись
        $rec = app(CarHistoryService::class)->createOrUpdate(
            HistoryCarDto::byRequest($data)
        );

        $admin = $builder->createRoleWithPerms([Permissions::USER_CAR_GET])->create();
        $this->loginAsAdmin($admin);

        $this->graphQL($this->getQueryStrWithHistory($car->id))
            ->assertJson([
                "data" => [
                    "car" => [
                        "id" => $car->id,
                        "history" => [
                            "id" => $rec->id,
                            "carUuid" => $carUuid,
                            "orders" => [
                                ["id" => $rec->orders->first()->id]
                            ],
                            "invoices" => [
                                ["id" => $rec->invoices->first()->id]
                            ]
                        ]
                    ]
                ]
            ])
        ;
    }

    /** @test */
    public function not_auth()
    {
        $user = User::factory()->create();

        $builder = $this->adminBuilder();
        $carBuilder = $this->carBuilder();
        $car = $carBuilder->setUserId($user->id)->create();

        $admin = $builder->createRoleWithPerms([Permissions::USER_CAR_GET])->create();

        $response = $this->graphQL($this->getQueryStr($car->id));

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not auth'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_AUTH, $response->json('errors.0.extensions.code'));
    }

    /** @test */
    public function not_perm()
    {
        $user = User::factory()->create();

        $builder = $this->adminBuilder();
        $carBuilder = $this->carBuilder();
        $car = $carBuilder->setUserId($user->id)->create();

        $admin = $builder->createRoleWithPerms([Permissions::USER_CAR_LIST])->create();
        $this->loginAsAdmin($admin);

        $response = $this->graphQL($this->getQueryStr($car->id));

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not perm'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_PERM, $response->json('errors.0.extensions.code'));
    }

    public static function getQueryStr($id): string
    {
        return  sprintf('{
            car (id: %d){
                id
                number
                vin
                brand {
                    name
                }
                model {
                    name
                }
                user {
                    name
                }
                deleteReason
                deleteComment
                hasInsurance
                insuranceFile {
                    id
                    url
                }
               }
            }',
            $id
        );
    }

    public static function getQueryStrWithRec($id): string
    {
        return  sprintf('{
            car (id: %d){
                id
                recommendations {
                    id
                }
               }
            }',
            $id
        );
    }

    public static function getQueryStrWithHistory($id): string
    {
        return  sprintf('{
            car (id: %d){
                id
                history {
                    id
                    carUuid
                    invoices {
                        id
                    }
                    orders {
                        id
                    }
                }
               }
            }',
            $id
        );
    }
}
