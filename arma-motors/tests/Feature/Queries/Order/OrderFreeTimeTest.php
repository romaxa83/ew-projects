<?php

namespace Tests\Feature\Queries\Order;

use App\Exceptions\ErrorsCode;
use App\Helpers\DateTime;
use App\Models\Catalogs\Service\Service;
use App\Models\Dealership\Dealership;
use App\Types\Order\Status;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\OrderBuilder;
use Tests\Traits\UserBuilder;

class OrderFreeTimeTest extends TestCase
{
    use DatabaseTransactions;
    use UserBuilder;
    use OrderBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
    }

    public function success()
    {
        $user = $this->userBuilder()->create();
        $this->loginAsUser($user);
        // diagnostic
        $service = Service::find(7);
        // have timeStep
        $dealership = Dealership::find(1);
        $date = 1630875600000;

        $data = [
            'serviceId' => $service->id,
            'dealershipId' => $dealership->id,
            'date' => (string)$date
        ];

        $response = $this->graphQL($this->getQueryStr($data))->assertOk();

        $responseData = $response->json('data.orderFreeTime');

        $this->assertArrayHasKey('values', $responseData);
        $this->assertNotEmpty($responseData['values']);

        $this->assertEquals(28800000, $responseData['values'][0]);
        $this->assertEquals(36000000, $responseData['values'][1]);
        $this->assertEquals(43200000, $responseData['values'][2]);
        $this->assertEquals(50400000, $responseData['values'][3]);
        $this->assertEquals(57600000, $responseData['values'][4]);
    }

    public function success_by_default_step()
    {
        $user = $this->userBuilder()->create();
        $this->loginAsUser($user);
        // diagnostic
        $service = Service::find(7);
        // not have timeStep
        $dealership = Dealership::find(2);
        $date = 1630875600000;

        $data = [
            'serviceId' => $service->id,
            'dealershipId' => $dealership->id,
            'date' => (string)$date
        ];

        $response = $this->graphQL($this->getQueryStr($data))->assertOk();

        $responseData = $response->json('data.orderFreeTime');

        $this->assertArrayHasKey('values', $responseData);
        $this->assertNotEmpty($responseData['values']);

        $this->assertEquals(28800000, $responseData['values'][0]);
        $this->assertEquals(32400000, $responseData['values'][1]);
        $this->assertEquals(36000000, $responseData['values'][2]);
        $this->assertEquals(39600000, $responseData['values'][3]);
        $this->assertEquals(43200000, $responseData['values'][4]);
    }

    public function success_by_default_step_without_service()
    {
        $orderBuilder = $this->orderBuilder();

        $user = $this->userBuilder()->create();
        $this->loginAsUser($user);
        // diagnostic
        $service = Service::find(7);
        // not have timeStep
        $dealership = Dealership::find(2);
        $date = 1630875600000;

        $data = [
            'serviceId' => $service->id,
            'dealershipId' => $dealership->id,
            'date' => (string)$date
        ];
        // 2021-09-06 09:00:00
         $timeService = DateTime::fromMillisecondToDate($date + 32400000);

        $orderBuilder
            ->setDealershipId($dealership->id)
            ->setStatus(Status::CREATED)
            ->setServiceId($service->id)
            ->setRealDate($timeService)
            ->asOne()
            ->create();

        $response = $this->graphQL($this->getQueryStr($data))->assertOk();

        $responseData = $response->json('data.orderFreeTime');

        $this->assertArrayHasKey('values', $responseData);
        $this->assertNotEmpty($responseData['values']);

        $this->assertEquals(28800000, $responseData['values'][0]);
        $this->assertEquals(36000000, $responseData['values'][1]);
        $this->assertEquals(39600000, $responseData['values'][2]);
        $this->assertEquals(43200000, $responseData['values'][3]);
    }

    public function success_by_default_step_another_service()
    {
        $orderBuilder = $this->orderBuilder();

        $user = $this->userBuilder()->create();
        $this->loginAsUser($user);
        // diagnostic
        $service = Service::find(7);
        // not have timeStep
        $dealership = Dealership::find(2);
        $date = 1630875600000;

        $data = [
            'serviceId' => $service->id,
            'dealershipId' => $dealership->id,
            'date' => (string)$date
        ];
        // 2021-09-06 09:00:00
        $timeService = DateTime::fromMillisecondToDate($date + 32400000);

        // body
        $anotherService = Service::find(2);
        $orderBuilder
            ->setDealershipId($dealership->id)
            ->setStatus(Status::CREATED)
            ->setServiceId($anotherService->id)
            ->setRealDate($timeService)
            ->asOne()
            ->create();

        $response = $this->graphQL($this->getQueryStr($data))->assertOk();

        $responseData = $response->json('data.orderFreeTime');

        $this->assertArrayHasKey('values', $responseData);
        $this->assertNotEmpty($responseData['values']);

        $this->assertEquals(28800000, $responseData['values'][0]);
        $this->assertEquals(32400000, $responseData['values'][1]);
        $this->assertEquals(36000000, $responseData['values'][2]);
        $this->assertEquals(39600000, $responseData['values'][3]);
        $this->assertEquals(43200000, $responseData['values'][4]);
    }

    public function not_schedule_to_department()
    {
        $user = $this->userBuilder()->create();
        $this->loginAsUser($user);
        // diagnostic
        $service = Service::find(7);
        // not have service department schedule
        $dealership = Dealership::find(3);
        $date = 1630875600000;

        $data = [
            'serviceId' => $service->id,
            'dealershipId' => $dealership->id,
            'date' => (string)$date
        ];

        $response = $this->graphQL($this->getQueryStr($data));

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(ErrorsCode::BAD_REQUEST, $response->json('errors.0.extensions.code'));
        $this->assertEquals(
            __('error.order.free time.not have schedule'),
            $response->json('errors.0.message')
        );
    }

    public function not_schedule_to_department_day()
    {
        $user = $this->userBuilder()->create();
        $this->loginAsUser($user);
        // body
        $service = Service::find(2);
        // not have body department schedule
        $dealership = Dealership::find(3);
        $date = 1630875600000;

        $data = [
            'serviceId' => $service->id,
            'dealershipId' => $dealership->id,
            'date' => (string)$date
        ];

        $response = $this->graphQL($this->getQueryStr($data));

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(ErrorsCode::BAD_REQUEST, $response->json('errors.0.extensions.code'));
        $this->assertEquals(
            __('error.order.free time.not have schedule'),
            $response->json('errors.0.message')
        );
    }

    public function not_service_support()
    {
        $user = $this->userBuilder()->create();
        $this->loginAsUser($user);
        // insurance
        $service = Service::find(3);
        // has timeStep
        $dealership = Dealership::find(1);
        $date = 1630875600000;

        $data = [
            'serviceId' => $service->id,
            'dealershipId' => $dealership->id,
            'date' => (string)$date
        ];

        $response = $this->graphQL($this->getQueryStr($data))->assertOk();

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(ErrorsCode::BAD_REQUEST, $response->json('errors.0.extensions.code'));
        $this->assertEquals(
            __('error.order.free time.not support this service'),
            $response->json('errors.0.message')
        );
    }

    public function fail_date_no_numeric()
    {
        $user = $this->userBuilder()->create();
        $this->loginAsUser($user);

        $service = Service::find(2);
        $dealership = Dealership::find(1);

        $data = [
            'serviceId' => $service->id,
            'dealershipId' => $dealership->id,
            'date' => 'fail'
        ];

        $response = $this->postGraphQL(['query' => $this->getQueryStr($data)]);

        $this->assertArrayHasKey('errors', $response->json());
        $this->assertEquals(ErrorsCode::BAD_REQUEST, $response->json('errors.0.extensions.code'));
        $this->assertEquals(
            __('validation.numeric', ['attribute' => __('validation.attributes.date')]),
            $response->json('errors.0.message')
        );
    }

    private function getQueryStr(array $data): string
    {
        return sprintf('
            {
                orderFreeTime(input:{
                    serviceId: %d
                    dealershipId: %d
                    date: "%s"
                }) {
                    values
                }
            }',
            $data['serviceId'],
            $data['dealershipId'],
            $data['date'],
        );
    }
}
