<?php

namespace Tests\Unit\Services\Onec\Command\Payment;

use App\Dto\Payments\PaymentCardDto;
use App\Enums\Requests\RequestCommand;
use App\Models\Dealers\Dealer;
use App\Models\Locations\State;
use App\Models\Request\Request;
use App\Services\OneC\Client\RequestClient;
use App\Services\OneC\Commands\Payment\AddCardToMember;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Builders\Dealers\DealerBuilder;
use Tests\Builders\Payment\PaymentCardBuilder;
use Tests\TestCase;

class AddCardToMemberTest extends TestCase
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
        $card = $this->paymentCardBuilder->setMember($model)->create();
        $dto = $this->dto();

        // эмулируем запрос к 1c
        $response = self::successResponseData();
        $sender = $this->createStub(RequestClient::class);
        $sender->method('postRequest')->willReturn($response);

        $this->assertNull($card->guid);
        $this->assertNull(Request::first());

        (new AddCardToMember($sender))->handler($card, ['dto' => $dto]);

        $record = Request::first();

        $this->assertEquals($record->status, Request::SUCCESS);
        $this->assertEquals($record->driver, Request::DRIVER_ONEC);
        $this->assertEquals($record->command, RequestCommand::ADD_PAYMENT_CARD_TO_MEMBER);
        $this->assertNotNull($record->response_data);
        $this->assertNotNull($record->send_data);

        $card->refresh();

        $this->assertEquals($card->guid, data_get($response, 'guid'));
    }

    /** @test */
    public function has_error()
    {
        $model = $this->dealerBuilder->create();
        $card = $this->paymentCardBuilder->setMember($model)->create();
        $dto = $this->dto();

        // эмулируем запрос к 1c
        $response = self::successResponseData();
        $response['success'] = false;
        $response['error'] = 'error message';
        $sender = $this->createStub(RequestClient::class);
        $sender->method('postRequest')->willReturn($response);

        $this->assertNull($card->guid);
        $this->assertNull(Request::first());

        (new AddCardToMember($sender))->handler($card, ['dto' => $dto]);

        $record = Request::first();

        $this->assertEquals($record->status, Request::ERROR);
        $this->assertEquals($record->driver, Request::DRIVER_ONEC);
        $this->assertEquals($record->response_data, $response['error']);
        $this->assertNotNull($record->send_data);

        $card->refresh();
        $this->assertNull($card->guid);
    }

    /** @test */
    public function check_transform_data()
    {
        /** @var $model Dealer */
        $model = $this->dealerBuilder->setData([
            'guid' => $this->faker->uuid
        ])->create();
        $card = $this->paymentCardBuilder->setMember($model)->create();
        $dto = $this->dto();

        $sender = $this->createStub(RequestClient::class);

        $command = (new AddCardToMember($sender));
        $data = $command->transformData($card, ['dto' => $dto]);
        /** @var $state State */
        $state = State::find($dto->billingAddress->stateID);

        $this->assertEquals(data_get($data, 'member.type'), $model::MORPH_NAME);
        $this->assertEquals(data_get($data, 'member.guid'), $model->guid);
        $this->assertEquals(data_get($data, 'payment_card.type'), $dto->type);
        $this->assertEquals(data_get($data, 'payment_card.name'), $dto->name);
        $this->assertEquals(data_get($data, 'payment_card.number'), $dto->number);
        $this->assertEquals(data_get($data, 'payment_card.cvc'), $dto->cvc);
        $this->assertEquals(data_get($data, 'payment_card.expiration_date'), $dto->expirationDate);
        $this->assertEquals(data_get($data, 'billing_address.country'), $state->country->country_code);
        $this->assertEquals(data_get($data, 'billing_address.state'), $state->short_name);
        $this->assertEquals(data_get($data, 'billing_address.city'), $dto->billingAddress->city);
        $this->assertEquals(data_get($data, 'billing_address.address_line_1'), $dto->billingAddress->addressLine1);
        $this->assertEquals(data_get($data, 'billing_address.address_line_2'), $dto->billingAddress->addressLine2);
        $this->assertEquals(data_get($data, 'billing_address.zip'), $dto->billingAddress->zip);
    }
    /** @test */
    public function fail_not_exist_dto()
    {
        /** @var $model Dealer */
        $model = $this->dealerBuilder->setData([
            'guid' => $this->faker->uuid
        ])->create();
        $card = $this->paymentCardBuilder->setMember($model)->create();
        $dto = $this->dto();

        $sender = $this->createStub(RequestClient::class);

        $command = (new AddCardToMember($sender));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("When generating data, there is no a dto");

        $data = $command->transformData($card, []);
    }

    /** @test */
    public function something_wrong()
    {
        $model = $this->dealerBuilder->create();
        $card = $this->paymentCardBuilder->setMember($model)->create();
        $dto = $this->dto();

        // эмулируем запрос к 1c
        $sender = $this->createStub(RequestClient::class);
        $sender->method('postRequest')->willThrowException(new \Exception('some_message'));

        (new AddCardToMember($sender))->handler($card, ['dto' => $dto]);

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

    public static function dto(): PaymentCardDto
    {
        return PaymentCardDto::byArgs([
            "payment_card" => [
                "type" => "Visa",
                "name" => "Dr. Winnifred Trantow V",
                "number" => "4485100135006686",
                "cvc" => "371",
                "expiration_date" => "12/24"
            ],
            "billing_address" => [
                "country_code" => "US",
                "state_id" => 1,
                "city" => "Violethaven",
                "address_line_1" => "Wava Inlet",
                "address_line_2" => "Kutch Hill",
                "zip" => "77230"
            ]
        ]);
    }
}

