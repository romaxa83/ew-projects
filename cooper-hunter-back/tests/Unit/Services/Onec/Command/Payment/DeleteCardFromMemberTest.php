<?php

namespace Tests\Unit\Services\Onec\Command\Payment;

use App\Dto\Payments\PaymentCardDto;
use App\Enums\Requests\RequestCommand;
use App\Models\Dealers\Dealer;
use App\Models\Locations\State;
use App\Models\Request\Request;
use App\Services\OneC\Client\RequestClient;
use App\Services\OneC\Commands\Payment\AddCardToMember;
use App\Services\OneC\Commands\Payment\DeleteCardFromMember;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Builders\Dealers\DealerBuilder;
use Tests\Builders\Payment\PaymentCardBuilder;
use Tests\TestCase;

class DeleteCardFromMemberTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    protected DealerBuilder $dealerBuilder;
    protected PaymentCardBuilder $paymentCardBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->dealerBuilder = resolve(DealerBuilder::class);
        $this->paymentCardBuilder = resolve(PaymentCardBuilder::class);
    }

    /** @test */
    public function success_send()
    {
        $model = $this->dealerBuilder->create();
        $card = $this->paymentCardBuilder->setData([
            'guid' => $this->faker->uuid
        ])->setMember($model)->create();

        // эмулируем запрос к 1c
        $response = self::successResponseData();
        $sender = $this->createStub(RequestClient::class);
        $sender->method('postRequest')->willReturn($response);

        $this->assertNotNull($card->guid);
        $this->assertNull(Request::first());

        (new DeleteCardFromMember($sender))->handler($card);

        $record = Request::first();

        $this->assertEquals($record->status, Request::SUCCESS);
        $this->assertEquals($record->driver, Request::DRIVER_ONEC);
        $this->assertEquals($record->command, RequestCommand::DELETE_PAYMENT_CARD_TO_MEMBER);
        $this->assertNotNull($record->response_data);
        $this->assertNotNull($record->send_data);
    }

    /** @test */
    public function has_error()
    {
        $model = $this->dealerBuilder->create();
        $card = $this->paymentCardBuilder->setData([
            'guid' => $this->faker->uuid
        ])->setMember($model)->create();

        // эмулируем запрос к 1c
        $response = self::successResponseData();
        $response['success'] = false;
        $response['error'] = 'error message';
        $sender = $this->createStub(RequestClient::class);
        $sender->method('postRequest')->willReturn($response);

        $this->assertNotNull($card->guid);
        $this->assertNull(Request::first());

        (new DeleteCardFromMember($sender))->handler($card);

        $record = Request::first();

        $this->assertEquals($record->status, Request::ERROR);
        $this->assertEquals($record->driver, Request::DRIVER_ONEC);
        $this->assertEquals($record->response_data, $response['error']);
        $this->assertNotNull($record->send_data);
    }

    /** @test */
    public function check_transform_data()
    {
        /** @var $model Dealer */
        $model = $this->dealerBuilder->setData([
            'guid' => $this->faker->uuid
        ])->create();
        $card = $this->paymentCardBuilder->setData([
            'guid' => $this->faker->uuid
        ])->setMember($model)->create();

        $sender = $this->createStub(RequestClient::class);

        $command = (new DeleteCardFromMember($sender));
        $data = $command->transformData($card);

        $this->assertEquals(data_get($data, 'guid'), $card->guid);
    }

    /** @test */
    public function something_wrong()
    {
        $model = $this->dealerBuilder->create();
        $card = $this->paymentCardBuilder->setData([
            'guid' => $this->faker->uuid
        ])->setMember($model)->create();

        // эмулируем запрос к 1c
        $sender = $this->createStub(RequestClient::class);
        $sender->method('postRequest')->willThrowException(new \Exception('some_message'));

        (new DeleteCardFromMember($sender))->handler($card);

        $record = Request::first();

        $this->assertEquals($record->status, Request::ERROR);
        $this->assertEquals($record->response_data, 'some_message');
    }

    public static function successResponseData(): array
    {
        return [
            "success" => true,
            "guid" => "70751f6f-9b0d-3c5a-99fc-307d043641d5",
            "error" => ""
        ];
    }
}


