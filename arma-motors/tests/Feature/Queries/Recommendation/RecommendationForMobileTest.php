<?php

namespace Tests\Feature\Queries\Recommendation;

use App\Models\Recommendation\Recommendation;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Arr;
use Tests\TestCase;
use Tests\Traits\Builders\RecommendationBuilder;
use Tests\Traits\CarBuilder;
use Tests\Traits\OrderBuilder;
use Tests\Traits\UserBuilder;

class RecommendationForMobileTest extends TestCase
{
    use DatabaseTransactions;
    use UserBuilder;
    use CarBuilder;
    use OrderBuilder;
    use RecommendationBuilder;

    const QUERY = 'recommendationsCurrent';

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    /** @test */
    public function success()
    {
        $user1 = $this->userBuilder()->create();
        $car1Uuid = "9ee4670f-0016-11ec-8274-4cd98fc26f15";
        $car1 = $this->carBuilder()->setUuid($car1Uuid)->setUserId($user1->id)->create();

        $this->loginAsUser($user1);
        // 1 - yes
        $this->recommendationBuilder()
            ->setUserId($user1->id)
            ->setCarUuid($car1->uuid)
            ->create();
        // 2 - yes
        $this->recommendationBuilder()
            ->setUserId($user1->id)
            ->setCarUuid($car1->uuid)
            ->create();
        // 3 - not
        $this->recommendationBuilder()->create();

        $res = $this->graphQL($this->getQueryStr($user1->id));

        $total = 2;
        $this->assertEquals($total, Arr::get($res, "data.".self::QUERY.".paginatorInfo.total"));
        $this->assertNotEquals($total, Recommendation::count());

    }

    /** @test */
    public function success_other_status()
    {
        $user1 = $this->userBuilder()->create();
        $car1Uuid = "9ee4670f-0016-11ec-8274-4cd98fc26f15";
        $car1 = $this->carBuilder()->setUuid($car1Uuid)->setUserId($user1->id)->create();

        $this->loginAsUser($user1);

        // 1 - yes
        $this->recommendationBuilder()
            ->setUserId($user1->id)
            ->setCarUuid($car1->uuid)
            ->create();
        // 2 - not
        $this->recommendationBuilder()
            ->setUserId($user1->id)
            ->setStatus(Recommendation::STATUS_USED)
            ->setCarUuid($car1->uuid)
            ->create();

        // 3 - not
        $this->recommendationBuilder()
            ->setUserId($user1->id)
            ->setStatus(Recommendation::STATUS_OLD)
            ->setCarUuid($car1->uuid)
            ->create();
        // 4 - not
        $this->recommendationBuilder()->create();

        $res = $this->graphQL($this->getQueryStr($user1->id));

        $total = 1;
        $this->assertEquals($total, Arr::get($res, "data.".self::QUERY.".paginatorInfo.total"));
        $this->assertNotEquals($total, Recommendation::count());
    }

    /** @test */
    public function success_other_user()
    {
        $user1 = $this->userBuilder()->create();
        $user2 = $this->userBuilder()->setPhone('38099999888871')->setEmail('test1@user.com')->create();
        $car1Uuid = "9ee4670f-0016-11ec-8274-4cd98fc26f15";
        $car2Uuid = "9ee4670f-0016-11ec-8274-4cd98fc26f15";
        $car1 = $this->carBuilder()->setUuid($car1Uuid)->setUserId($user1->id)->create();
        $car2 = $this->carBuilder()->setUuid($car2Uuid)->setUserId($user2->id)->create();

        $this->loginAsUser($user1);

        // 1 - yes
        $this->recommendationBuilder()
            ->setUserId($user1->id)
            ->setCarUuid($car1->uuid)
            ->create();
        // 2 - yes
        $this->recommendationBuilder()
            ->setUserId($user1->id)
            ->setCarUuid($car1->uuid)
            ->create();

        // 3 - not
        $this->recommendationBuilder()
            ->setUserId($user2->id)
            ->setCarUuid($car2->uuid)
            ->create();
        // 4 - not
        $this->recommendationBuilder()->create();

        $res = $this->graphQL($this->getQueryStr($user1->id));

        $total = 2;
        $this->assertEquals($total, Arr::get($res, "data.".self::QUERY.".paginatorInfo.total"));
        $this->assertNotEquals($total, Recommendation::count());
    }

    /** @test */
    public function success_empty()
    {
        $user1 = $this->userBuilder()->create();
        $user2 = $this->userBuilder()->setPhone('38099999888871')->setEmail('test1@user.com')->create();
        $car1Uuid = "9ee4670f-0016-11ec-8274-4cd98fc26f15";
        $car1 = $this->carBuilder()->setUuid($car1Uuid)->setUserId($user1->id)->create();

        $this->loginAsUser($user1);

        // 1 - not
        $this->recommendationBuilder()
            ->setUserId($user1->id)
            ->setCarUuid($car1->uuid)
            ->create();
        // 2 - not
        $this->recommendationBuilder()
            ->setUserId($user1->id)
            ->setCarUuid($car1->uuid)
            ->create();

        // 4 - not
        $this->recommendationBuilder()->create();

        $res = $this->graphQL($this->getQueryStr($user2->id));

        $total = 0;
        $this->assertEquals($total, Arr::get($res, "data.".self::QUERY.".paginatorInfo.total"));
        $this->assertNotEquals($total, Recommendation::count());
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

