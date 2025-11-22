<?php

namespace Tests\Feature\Queries\Agreement;

use App\Models\Agreement\Agreement;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Builders\AgreementBuilder;
use Tests\Traits\CarBuilder;
use Tests\Traits\OrderBuilder;
use Tests\Traits\UserBuilder;

class AgreementForMobileTest extends TestCase
{
    use DatabaseTransactions;
    use UserBuilder;
    use CarBuilder;
    use OrderBuilder;
    use AgreementBuilder;

    const QUERY = 'agreementsCurrent';

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function success()
    {
        $userUuid = "8ee4670f-0016-11ec-8274-4cd98fc26f15";
        $user = $this->userBuilder()->setUuid($userUuid)->create();
        $carUuid = "9ee4670f-0016-11ec-8274-4cd98fc26f15";
        $car = $this->carBuilder()->setUuid($carUuid)->create();

        $this->loginAsUser($user);

        // yes
        $this->agreementBuilder()
            ->setUserUuid($userUuid)
            ->setCarUuid($carUuid)
            ->create();
        // yes
        $this->agreementBuilder()
            ->setUserUuid($userUuid)
            ->setCarUuid($carUuid)
            ->create();
        // no
        $this->agreementBuilder()
            ->setCarUuid($carUuid)
            ->create();

        $this->graphQL($this->getQueryStr($user->id))
            ->assertJson([
                "data" => [
                    "agreementsCurrent" => [
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
    public function success_other_status()
    {
        $userUuid = "8ee4670f-0016-11ec-8274-4cd98fc26f15";
        $user = $this->userBuilder()->setUuid($userUuid)->create();
        $carUuid = "9ee4670f-0016-11ec-8274-4cd98fc26f15";
        $car = $this->carBuilder()->setUuid($carUuid)->create();

        $this->loginAsUser($user);

        // yes
        $this->agreementBuilder()
            ->setUserUuid($userUuid)
            ->setCarUuid($carUuid)
            ->create();
        // not
        $this->agreementBuilder()
            ->setStatus(Agreement::STATUS_USED)
            ->setUserUuid($userUuid)
            ->setCarUuid($carUuid)
            ->create();
        // not
        $this->agreementBuilder()
            ->setStatus(Agreement::STATUS_USED)
            ->setUserUuid($userUuid)
            ->setCarUuid($carUuid)
            ->create();
        // not
        $this->agreementBuilder()
            ->setCarUuid($carUuid)
            ->create();

        $this->graphQL($this->getQueryStr($user->id))
            ->assertJson([
                "data" => [
                    "agreementsCurrent" => [
                        "paginatorInfo" => [
                            "count" => 1,
                            "total" => 1,
                        ]
                    ]
                ]
            ])
        ;
    }

    /** @test */
    public function success_other_user()
    {
        $userUuid = "8ee4670f-0016-11ec-8274-4cd98fc26f15";
        $userUuid2 = "7ee4670f-0016-11ec-8274-4cd98fc26f15";
        $user = $this->userBuilder()->setUuid($userUuid)->create();
        $user2 = $this->userBuilder()->setPhone('38099999888871')
            ->setEmail('test1@user.com')->setUuid($userUuid2)->create();
        $carUuid = "9ee4670f-0016-11ec-8274-4cd98fc26f15";
        $car = $this->carBuilder()->setUuid($carUuid)->create();

        $this->loginAsUser($user);

        // yes
        $this->agreementBuilder()
            ->setUserUuid($userUuid)
            ->setCarUuid($carUuid)
            ->create();
        // not
        $this->agreementBuilder()
            ->setUserUuid($userUuid2)
            ->setCarUuid($carUuid)
            ->create();
        // not
        $this->agreementBuilder()
            ->setCarUuid($carUuid)
            ->create();

        $this->graphQL($this->getQueryStr($user->id))
            ->assertJson([
                "data" => [
                    "agreementsCurrent" => [
                        "paginatorInfo" => [
                            "count" => 1,
                            "total" => 1,
                        ]
                    ]
                ]
            ])
        ;
    }

    /** @test */
    public function success_empty()
    {
        $userUuid = "8ee4670f-0016-11ec-8274-4cd98fc26f15";
        $userUuid2 = "7ee4670f-0016-11ec-8274-4cd98fc26f15";
        $user = $this->userBuilder()->setUuid($userUuid)->create();
        $user2 = $this->userBuilder()->setPhone('38099999888871')
            ->setEmail('test1@user.com')->setUuid($userUuid2)->create();
        $carUuid = "9ee4670f-0016-11ec-8274-4cd98fc26f15";
        $car = $this->carBuilder()->setUuid($carUuid)->create();

        $this->loginAsUser($user);

        // not
        $this->agreementBuilder()
            ->setUserUuid($userUuid2)
            ->setCarUuid($carUuid)
            ->create();
        // not
        $this->agreementBuilder()
            ->setUserUuid($userUuid2)
            ->setCarUuid($carUuid)
            ->create();
        // not
        $this->agreementBuilder()
            ->setCarUuid($carUuid)
            ->create();

        $this->graphQL($this->getQueryStr($user->id))
            ->assertJson([
                "data" => [
                    "agreementsCurrent" => [
                        "paginatorInfo" => [
                            "count" => 0,
                            "total" => 0,
                        ]
                    ]
                ]
            ])
        ;
    }

    public function getQueryStr(string $id): string
    {
        return  sprintf('{
            %s (userId: %s) {
                data {
                    id
                }
                paginatorInfo {
                    count
                    total
                }
               }
            }',
            self::QUERY,
            $id
        );
    }
}
