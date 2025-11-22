<?php

namespace Tests\Unit\Services\AA\Commands;

use App\Models\AA\AAResponse;
use App\Models\Agreement\Agreement;
use App\Services\AA\Client\RequestClient;
use App\Services\AA\Commands\AcceptAgreement;
use App\Services\AA\Exceptions\AARequestException;
use App\Services\AA\ResponseService;
use App\Services\Order\AgreementService;
use App\Services\Order\OrderService;
use App\Types\Order\Status;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Builders\AgreementBuilder;
use Tests\Traits\CarBuilder;
use Tests\Traits\OrderBuilder;
use Tests\Traits\UserBuilder;

class AcceptAgreementTest extends TestCase
{
    use DatabaseTransactions;
    use UserBuilder;
    use CarBuilder;
    use AgreementBuilder;
    use OrderBuilder;

    /** @test */
    public function success()
    {
        $response = self::successData();

        $carUuid = "9ee4670f-0016-11ec-8274-4cd98fc26f15";
        $car = $this->carBuilder()->setUuid($carUuid)->create();
        $userUuid = "8ee4670f-0016-11ec-8274-4cd98fc26f15";
        $user = $this->userBuilder()->setUuid($userUuid)->create();
        $orderUuid = "8ee4670f-0016-11ec-8274-4cd98fc26f15";
        /** @var $model Agreement */
        $model = $this->agreementBuilder()
            ->setCarUuid($carUuid)
            ->setUserUuid($userUuid)
            ->setBaseOrderUuid($orderUuid)
            ->create();

        $this->assertNull($model->accepted_at);
        $this->assertNotEquals($model->status, Agreement::STATUS_VERIFY);

        $order = $this->orderBuilder()->setUuid($orderUuid)->asOne()->create();

        $this->assertNotEquals($model->status, Agreement::STATUS_VERIFY);
        $this->assertNotEquals($order->status, Status::CREATED);

        $this->assertEquals($order->agreements[0]->id, $model->id);
        $this->assertEmpty($order->agreementsAccept);

        // эмулируем запрос к AA
        $sender = $this->createStub(RequestClient::class);
        $sender->method('postRequest')->willReturn($response);

        $this->assertEmpty($user->aaResponses);

        (new AcceptAgreement(
            $sender,
            resolve(ResponseService::class),
            resolve(OrderService::class),
            resolve(AgreementService::class),
        ))->handler($model);

        $model->refresh();
        $user->refresh();
        $order->refresh();

        $this->assertEquals($model->status, Agreement::STATUS_VERIFY);
        $this->assertNotNull($model->accepted_at);
//        $this->assertEquals($order->status, Status::CREATED);

        $this->assertNotEmpty($order->agreementsAccept);
        $this->assertEquals($order->agreementsAccept[0]->id, $model->id);

        $this->assertNotEmpty($user->aaResponses);
        $this->assertNotEmpty($user->aaResponses[0]->status, AAResponse::STATUS_SUCCESS);
        $this->assertNotEmpty($user->aaResponses[0]->type, AAResponse::TYPE_ACCEPT_AGREEMENT);
    }

    /** @test */
    public function something_wrong()
    {
        $carUuid = "9ee4670f-0016-11ec-8274-4cd98fc26f15";
        $car = $this->carBuilder()->setUuid($carUuid)->create();
        $userUuid = "8ee4670f-0016-11ec-8274-4cd98fc26f15";
        $user = $this->userBuilder()->setUuid($userUuid)->create();
        /** @var $model Agreement */
        $model = $this->agreementBuilder()
            ->setCarUuid($carUuid)
            ->setUserUuid($userUuid)
            ->create();

        $order = $this->orderBuilder()->setUuid($model->uuid)->asOne()->create();
        $model->refresh();

        // эмулируем запрос к AA
        $sender = $this->createStub(RequestClient::class);
        $sender->method('postRequest')
            ->willThrowException(new AARequestException());

        $this->assertEmpty($user->aaResponses);
        $this->assertNotEmpty($model->order);

        (new AcceptAgreement(
            $sender,
            resolve(ResponseService::class),
            resolve(OrderService::class),
            resolve(AgreementService::class),
        ))->handler($model);

        $model->refresh();
        $user->refresh();

        $this->assertEmpty($model->order);
        $this->assertEquals($model->status, Agreement::STATUS_ERROR);

        $this->assertNotEmpty($user->aaResponses);
        $this->assertEquals($user->aaResponses[0]->status, AAResponse::STATUS_ERROR);
    }

    public static function successData(): array
    {
        return [
            "success" => true,
            "data" => null,
            "message" => ""
        ];
    }
}

