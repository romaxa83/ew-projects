<?php

namespace Tests\Feature\Queries\Order;

use App\Exceptions\ErrorsCode;
use App\Types\Order\Status;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\OrderBuilder;
use Tests\Traits\Statuses;
use Tests\Traits\UserBuilder;

class OrderCurrentListTest extends TestCase
{
    use DatabaseTransactions;
    use UserBuilder;
    use OrderBuilder;
    use Statuses;

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
        $orderBuilder->setUserId($user->id)->setStatus(Status::DONE)->setCount(2)->create();
        $orderBuilder->setUserId($user->id)->setStatus(Status::IN_PROCESS)->setCount(2)->create();

        $orderBuilder->setUserId($user2->id)->setStatus(Status::CLOSE)->setCount(4)->create();


        $response = $this->graphQL($this->getQueryStr($user->id));

        $this->assertEquals(6, $response->json('data.ordersCurrent.paginatorInfo.total'));
    }

    /** @test */
    public function get_period_today_by_date()
    {
        $userBuilder = $this->userBuilder();
        $orderBuilder = $this->orderBuilder();

        $user = $userBuilder->create();
        $this->loginAsUser($user);

        $date1 = Carbon::now()->addMinutes(10);
        $date2 = Carbon::now()->addDays(2);
        $date3 = Carbon::now()->subMinutes(10);

        $orderBuilder->setUserId($user->id)->setStatus(Status::IN_PROCESS)->setOnDate($date1)->asOne()->create();
        $orderBuilder->setUserId($user->id)->setStatus(Status::IN_PROCESS)->setOnDate($date2)->asOne()->create();
        $orderBuilder->setUserId($user->id)->setStatus(Status::DONE)->setOnDate($date3)->asOne()->create();
        $orderBuilder->setUserId($user->id)->setStatus(Status::CREATED)->setOnDate(null)->asOne()->create();

        $response = $this->graphQL($this->getQueryStrPeriod($user->id, $this->order_period_today));
        $this->assertEquals(1, $response->json('data.ordersCurrent.paginatorInfo.total'));
    }

    /** @test */
    public function get_period_today_by_date_and_real_date()
    {
        $userBuilder = $this->userBuilder();
        $orderBuilder = $this->orderBuilder();

        $user = $userBuilder->create();
        $this->loginAsUser($user);

        $date1 = Carbon::now()->addMinutes(10);
        $date1_1 = Carbon::now()->addMinutes(60);
        $date2 = Carbon::now()->addDays(2);
        $date3 = Carbon::now()->subMinutes(10);

        $orderBuilder->setUserId($user->id)->setStatus(Status::IN_PROCESS)->setOnDate($date1)->asOne()->create();
        $orderBuilder->setUserId($user->id)->setStatus(Status::IN_PROCESS)->setRealDate($date1_1)->asOne()->create();
        $orderBuilder->setUserId($user->id)->setStatus(Status::IN_PROCESS)->setOnDate($date2)->asOne()->create();
        $orderBuilder->setUserId($user->id)->setStatus(Status::DONE)->setOnDate($date3)->asOne()->create();
        $orderBuilder->setUserId($user->id)->setStatus(Status::CREATED)->setOnDate(null)->asOne()->create();

        $response = $this->graphQL($this->getQueryStrPeriod($user->id, $this->order_period_today));
        $this->assertEquals(2, $response->json('data.ordersCurrent.paginatorInfo.total'));
    }

    /** @test */
    public function get_period_incoming()
    {
        $userBuilder = $this->userBuilder();
        $orderBuilder = $this->orderBuilder();

        $user = $userBuilder->create();
        $this->loginAsUser($user);

        $date1 = Carbon::now()->addMinutes(10);
        $date2 = Carbon::now()->addDays(2);
        $date3 = Carbon::now()->addDays(3);

        $orderBuilder->setUserId($user->id)->setStatus(Status::IN_PROCESS)->setOnDate($date1)->asOne()->create();
        $orderBuilder->setUserId($user->id)->setStatus(Status::IN_PROCESS)->setOnDate($date2)->asOne()->create();
        $orderBuilder->setUserId($user->id)->setStatus(Status::DONE)->setOnDate($date3)->asOne()->create();
        $orderBuilder->setUserId($user->id)->setStatus(Status::CREATED)->setOnDate(null)->asOne()->create();

        $response = $this->graphQL($this->getQueryStrPeriod($user->id, $this->order_period_incoming));
        $this->assertEquals(2, $response->json('data.ordersCurrent.paginatorInfo.total'));
    }

    /** @test */
    public function get_period_incoming_real_date()
    {
        $userBuilder = $this->userBuilder();
        $orderBuilder = $this->orderBuilder();

        $user = $userBuilder->create();
        $this->loginAsUser($user);

        $date1 = Carbon::now()->addMinutes(10);
        $date2 = Carbon::now()->addDays(2);
        $date3 = Carbon::now()->addDays(3);

        $orderBuilder->setUserId($user->id)->setStatus(Status::IN_PROCESS)->setOnDate($date1)->setRealDate($date2)->asOne()->create();
        $orderBuilder->setUserId($user->id)->setStatus(Status::IN_PROCESS)->setOnDate($date2)->asOne()->create();
        $orderBuilder->setUserId($user->id)->setStatus(Status::DONE)->setOnDate($date3)->asOne()->create();
        $orderBuilder->setUserId($user->id)->setStatus(Status::CREATED)->setOnDate(null)->asOne()->create();

        $response = $this->graphQL($this->getQueryStrPeriod($user->id, $this->order_period_incoming));
        $this->assertEquals(3, $response->json('data.ordersCurrent.paginatorInfo.total'));
    }

    /** @test */
    public function get_by_order()
    {
        $userBuilder = $this->userBuilder();
        $orderBuilder = $this->orderBuilder();

        $user = $userBuilder->create();
        $this->loginAsUser($user);

        $date1 = Carbon::now()->addMinutes(10);
        $date2 = Carbon::now()->addDays(2);
        $date3 = Carbon::now()->addDays(3);

        $orderBuilder->setUserId($user->id)->setStatus(Status::IN_PROCESS)->setOnDate($date1)->asOne()->create();
        $orderBuilder->setUserId($user->id)->setStatus(Status::IN_PROCESS)->setOnDate($date2)->asOne()->create();
        $orderBuilder->setUserId($user->id)->setStatus(Status::DONE)->setOnDate($date3)->asOne()->create();
        $orderBuilder->setUserId($user->id)->setStatus(Status::CREATED)->setOnDate(null)->asOne()->create();

        $response = $this->graphQL($this->getQueryStrPeriodAndOrderBy($user->id, $this->order_period_incoming, 'ASC'));
        $ascId = $response->json('data.ordersCurrent.data.0.id');

        $response = $this->graphQL($this->getQueryStrPeriodAndOrderBy($user->id, $this->order_period_incoming, 'DESC'));
        $descId = $response->json('data.ordersCurrent.data.0.id');

        $this->assertNotNull($ascId);
        $this->assertNotNull($descId);
        $this->assertNotEquals($ascId, $descId);
    }

    /** @test */
    public function get_by_order_real_date()
    {
        $userBuilder = $this->userBuilder();
        $orderBuilder = $this->orderBuilder();

        $user = $userBuilder->create();
        $this->loginAsUser($user);

        $date1 = Carbon::now()->addDays(3);
        $date2 = Carbon::now()->addDays(2);

        $orderBuilder->setUserId($user->id)->setStatus(Status::IN_PROCESS)->setOnDate($date1)->asOne()->create();
        $orderBuilder->setUserId($user->id)->setStatus(Status::IN_PROCESS)->setRealDate($date2)->asOne()->create();
        $orderBuilder->setUserId($user->id)->setStatus(Status::CREATED)->setOnDate(null)->asOne()->create();

        $response = $this->graphQL($this->getQueryStrPeriodAndOrderBy($user->id, $this->order_period_incoming, 'ASC'));
        $ascId = $response->json('data.ordersCurrent.data.0.id');

        $response = $this->graphQL($this->getQueryStrPeriodAndOrderBy($user->id, $this->order_period_incoming, 'DESC'));
        $descId = $response->json('data.ordersCurrent.data.0.id');

        $this->assertNotNull($ascId);
        $this->assertNotNull($descId);
        $this->assertNotEquals($ascId, $descId);
    }

    /** @test */
    public function get_period_incoming_nothing()
    {
        $userBuilder = $this->userBuilder();
        $orderBuilder = $this->orderBuilder();

        $user = $userBuilder->create();
        $this->loginAsUser($user);

        $date1 = Carbon::now()->addMinutes(10);
        $date2 = Carbon::now()->addMinutes(60);

        $orderBuilder->setUserId($user->id)->setStatus(Status::IN_PROCESS)->setOnDate($date1)->asOne()->create();
        $orderBuilder->setUserId($user->id)->setStatus(Status::DONE)->setOnDate($date2)->asOne()->create();

        $response = $this->graphQL($this->getQueryStrPeriod($user->id, $this->order_period_incoming));
        $this->assertEquals(0, $response->json('data.ordersCurrent.paginatorInfo.total'));
    }

    /** @test */
    public function not_auth()
    {
        $userBuilder = $this->userBuilder();
        $orderBuilder = $this->orderBuilder();

        $user = $userBuilder->create();

        $date1 = Carbon::now()->addMinutes(10);
        $date2 = Carbon::now()->addMinutes(60);

        $orderBuilder->setUserId($user->id)->setStatus(Status::IN_PROCESS)->setOnDate($date1)->asOne()->create();
        $orderBuilder->setUserId($user->id)->setStatus(Status::DONE)->setOnDate($date2)->asOne()->create();

        $response = $this->graphQL($this->getQueryStrPeriod($user->id, $this->order_period_incoming));

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not auth'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_AUTH, $response->json('errors.0.extensions.code'));
    }

    public static function getQueryStr($userId): string
    {
        return  sprintf('{
            ordersCurrent(userId: %s) {
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

    public static function getQueryStrPeriod($userId, $period): string
    {
        return  sprintf('{
            ordersCurrent(userId: %s, period: %s) {
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
            $period
        );
    }

    public static function getQueryStrPeriodAndOrderBy($userId, $period, $orderType): string
    {
        return  sprintf('{
            ordersCurrent(userId: %s, period: %s, orderByOnDateAndReal: %s) {
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
            $period,
            $orderType,
        );
    }
}
