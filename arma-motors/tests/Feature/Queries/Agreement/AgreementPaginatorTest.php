<?php

namespace Tests\Feature\Queries\Agreement;

use App\Exceptions\ErrorsCode;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\Builders\AgreementBuilder;
use Tests\Traits\CarBuilder;
use Tests\Traits\UserBuilder;

class AgreementPaginatorTest extends TestCase
{
    use DatabaseTransactions;
    use AdminBuilder;
    use CarBuilder;
    use UserBuilder;
    use AgreementBuilder;

    const QUERY = 'agreements';

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function success()
    {
        $admin = $this->adminBuilder()->create();
        $this->loginAsAdmin($admin);

        $userUuid = "8ee4670f-0016-11ec-8274-4cd98fc26f15";
        $user = $this->userBuilder()->setUuid($userUuid)->create();
        $carUuid = "9ee4670f-0016-11ec-8274-4cd98fc26f15";
        $car = $this->carBuilder()->setUuid($carUuid)->create();

        $this->agreementBuilder()
            ->setUserUuid($userUuid)
            ->setCarUuid($carUuid)
            ->create();

        $this->agreementBuilder()
            ->setUserUuid($userUuid)
            ->setCarUuid($carUuid)
            ->create();

        $this->graphQL($this->getQueryStr())
            ->assertJsonStructure([
                "data" => [
                    self::QUERY =>  [
                        "data" => [
                            "*" => ["id"]
                        ],
                        "paginatorInfo" => [
                            "count",
                            "total",
                        ]
                    ]
                ]
            ])
            ->assertJson([
                "data" => [
                    self::QUERY => [
                        "paginatorInfo" => [
                            "count" => 2,
                            "total" => 2,
                        ]
                    ]
                ]
            ])
        ;
    }

    /** @test */
    public function not_model()
    {
        $admin = $this->adminBuilder()->create();
        $this->loginAsAdmin($admin);

        $this->graphQL($this->getQueryStr())
            ->assertJson([
                "data" => [
                    self::QUERY => [
                        "paginatorInfo" => [
                            "count" => 0,
                            "total" => 0,
                        ]
                    ]
                ]
            ])
        ;
    }


    public function not_auth()
    {
        $this->graphQL($this->getQueryStr())
            ->assertJson([
                "errors" => [
                    0 => [
                        "message" => __('auth.not auth'),
                        "extensions" => [
                            "code" => ErrorsCode::NOT_AUTH
                        ]
                    ]
                ]
            ])
        ;
    }

    public function getQueryStr(): string
    {
        return  sprintf('{
            %s {
                data{
                    id
                }
                paginatorInfo {
                    count
                    total
                }
               }
            }',
            self::QUERY,
        );
    }
}



