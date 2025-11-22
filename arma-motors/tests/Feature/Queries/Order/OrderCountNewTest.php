<?php

namespace Tests\Feature\Queries\Order;

use App\Exceptions\ErrorsCode;
use App\Models\Catalogs\Service\Service;
use App\Types\Order\Status;
use App\Types\Permissions;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\AdminBuilder;
use Tests\Traits\OrderBuilder;

class OrderCountNewTest extends TestCase
{
    use DatabaseTransactions;
    use OrderBuilder;
    use AdminBuilder;

    /** @test */
    public function success_see_all()
    {
        $admin = $this->adminBuilder()
            ->createRoleWithPerm(Permissions::ORDER_CAN_SEE)
            ->create();
        $this->loginAsAdmin($admin);

        $countNew = 2;

        $this->orderBuilder()->setStatus(Status::DRAFT)->setCount($countNew)->create();
        $this->orderBuilder()->setStatus(Status::CREATED)->setCount(4)->create();
        $this->orderBuilder()->setStatus(Status::IN_PROCESS)->setCount(4)->create();
        $this->orderBuilder()->setStatus(Status::DONE)->setCount(4)->create();
        $this->orderBuilder()->setStatus(Status::REJECT)->setCount(4)->create();
        $this->orderBuilder()->setStatus(Status::CLOSE)->setCount(4)->create();

        $response = $this->graphQL(self::getQueryStr());

        $count = $response->json('data.orderCountNew.name');

        $this->assertEquals($countNew, $count);
    }

    /** @test */
    public function success_only_own_order_by_service()
    {
        // проверяем кол-во новых заявок, по сервису пользователя

        $serviceAnother = Service::find(1);
        $service = Service::find(2);

        $admin = $this->adminBuilder()
            ->setService($service->id)
            ->create();

        $this->loginAsAdmin($admin);

        $countNew = 2;

        $this->orderBuilder()->setStatus(Status::DRAFT)->setServiceId($serviceAnother->id)
            ->setCount(1)->create();
        $this->orderBuilder()->setStatus(Status::DRAFT)->setServiceId($service->id)
            ->setCount($countNew)->create();
        $this->orderBuilder()->setStatus(Status::CREATED)->setCount(3)->create();

        $response = $this->graphQL(self::getQueryStr());

        $count = $response->json('data.orderCountNew.name');
        $this->assertEquals($countNew, $count);
    }

    /** @test */
    public function nothing()
    {
        $admin = $this->adminBuilder()->create();
        $this->loginAsAdmin($admin);

        $countNew = 0;

        $response = $this->graphQL(self::getQueryStr());

        $count = $response->json('data.orderCountNew.name');

        $this->assertEquals($countNew, $count);
    }

    /** @test */
    public function not_auth()
    {
        $this->adminBuilder()->create();

        $response = $this->graphQL(self::getQueryStr());

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(__('auth.not auth'), $response->json('errors.0.message'));
        $this->assertEquals(ErrorsCode::NOT_AUTH, $response->json('errors.0.extensions.code'));
    }

    public static function getQueryStr(): string
    {
        return  sprintf('{
            orderCountNew {
                key
                name
               }
            }',
        );
    }
}

