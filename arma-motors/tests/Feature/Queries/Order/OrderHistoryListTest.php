<?php

namespace Tests\Feature\Queries\Order;

use App\Exceptions\ErrorsCode;
use App\Helpers\DateTime;
use App\Types\Order\Status;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\OrderBuilder;
use Tests\Traits\UserBuilder;

class OrderHistoryListTest extends TestCase
{
    use DatabaseTransactions;
    use UserBuilder;
    use OrderBuilder;

    /** @test */
    public function success()
    {
        $userBuilder = $this->userBuilder();
        $orderBuilder = $this->orderBuilder();

        $user = $userBuilder->create();
        $this->loginAsUser($user);

        $user2 = $userBuilder->setEmail('test2@user.com')->setPhone('38099999888873')->create();

        $orderBuilder->setUserId($user->id)->setStatus(Status::CLOSE)->setCount(3)->create();
        $orderBuilder->setUserId($user->id)->setStatus(Status::CREATED)->setCount(2)->create();

        $orderBuilder->setUserId($user2->id)->setStatus(Status::CLOSE)->setCount(4)->create();


        $response = $this->graphQL($this->getQueryStr($user->id));

        $this->assertEquals(3, $response->json('data.ordersHistory.paginatorInfo.total'));
    }

    /** @test */
    public function get_from()
    {
        $userBuilder = $this->userBuilder();
        $orderBuilder = $this->orderBuilder();

        $user = $userBuilder->create();
        $this->loginAsUser($user);

        $date1 = Carbon::now()->addMinute();
        $date2 = Carbon::now()->addDay();
        $date3 = Carbon::now()->subDay();

        $orderBuilder->setUserId($user->id)->setStatus(Status::CLOSE)->setClosedAt($date1)->asOne()->create();
        $orderBuilder->setUserId($user->id)->setStatus(Status::CLOSE)->setClosedAt($date2)->asOne()->create();
        $orderBuilder->setUserId($user->id)->setStatus(Status::CLOSE)->setClosedAt($date3)->asOne()->create();

        $response = $this->graphQL($this->getQueryStrFrom($user->id, DateTime::fromDateToMillisecond(Carbon::now())));
        $this->assertEquals(2, $response->json('data.ordersHistory.paginatorInfo.total'));

        $response = $this->graphQL($this->getQueryStrFrom($user->id, DateTime::fromDateToMillisecond(Carbon::now()->addMinutes(10))));
        $this->assertEquals(1, $response->json('data.ordersHistory.paginatorInfo.total'));
    }

    /** @test */
    public function get_from_and_to()
    {
        $userBuilder = $this->userBuilder();
        $orderBuilder = $this->orderBuilder();

        $user = $userBuilder->create();
        $this->loginAsUser($user);

        $date1 = Carbon::now()->addMinute();
        $date2 = Carbon::now()->addDay();
        $date3 = Carbon::now()->subDay();

        $orderBuilder->setUserId($user->id)->setStatus(Status::CLOSE)->setClosedAt($date1)->asOne()->create();
        $orderBuilder->setUserId($user->id)->setStatus(Status::CLOSE)->setClosedAt($date2)->asOne()->create();
        $orderBuilder->setUserId($user->id)->setStatus(Status::CLOSE)->setClosedAt($date3)->asOne()->create();

        $response = $this->graphQL($this->getQueryStrFromAndTo(
            $user->id,
            DateTime::fromDateToMillisecond(Carbon::now()),
            DateTime::fromDateToMillisecond(Carbon::now()->addMinutes(10))
        ));
        $this->assertEquals(1, $response->json('data.ordersHistory.paginatorInfo.total'));
    }

    /** @test */
    public function not_auth()
    {
        $userBuilder = $this->userBuilder();
        $orderBuilder = $this->orderBuilder();

        $user = $userBuilder->create();

        $user2 = $userBuilder->setEmail('test2@user.com')->setPhone('38099999888873')->create();

        $orderBuilder->setUserId($user->id)->setStatus(Status::CLOSE)->setCount(3)->create();
        $orderBuilder->setUserId($user->id)->setStatus(Status::CREATED)->setCount(2)->create();

        $orderBuilder->setUserId($user2->id)->setStatus(Status::CLOSE)->setCount(4)->create();

        $response = $this->graphQL($this->getQueryStr($user->id));

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not auth'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_AUTH, $response->json('errors.0.extensions.code'));
    }

    public static function getQueryStr($userId): string
    {
        return  sprintf('{
            ordersHistory(userId: %s) {
                data{
                    id
                }
                paginatorInfo {
                    total
                    count
                }
               }
            }',
        $userId
        );
    }

    public static function getQueryStrFrom($userId, $from): string
    {
        return  sprintf('{
            ordersHistory(userId: %s, from: "%s") {
                data{
                    id
                }
                paginatorInfo {
                    total
                    count
                }
               }
            }',
            $userId,
            $from
        );
    }

    public static function getQueryStrFromAndTo($userId, $from, $to): string
    {
        return  sprintf('{
            ordersHistory(userId: %s, from: "%s", to: "%s") {
                data{
                    id
                }
                paginatorInfo {
                    total
                    count
                }
               }
            }',
            $userId,
            $from,
            $to
        );
    }
}
