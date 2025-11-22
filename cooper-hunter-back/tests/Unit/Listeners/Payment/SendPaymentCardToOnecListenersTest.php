<?php

namespace Tests\Unit\Listeners\Payment;

use App\Dto\Payments\PaymentCardDto;
use App\Events\Payments\AddPaymentCardToMemberEvent;
use App\Events\Payments\DeletePaymentCardFromMemberEvent;
use App\Listeners\Payments\SendPaymentCardToOnecListeners;
use App\Models\Dealers\Dealer;
use App\Models\Request\Request;
use App\Services\OneC\RequestService;
use Core\Exceptions\TranslatedException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery\MockInterface;
use Tests\Builders\Dealers\DealerBuilder;
use Tests\Builders\Payment\PaymentCardBuilder;
use Tests\TestCase;

class SendPaymentCardToOnecListenersTest extends TestCase
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
    public function success_create()
    {
        /** @var $model Dealer */
        $model = $this->dealerBuilder->create();
        $card = $this->paymentCardBuilder->setMember($model)->create();
        $dto = $this->dto();

        $this->assertNull(Request::first());

        $event = new AddPaymentCardToMemberEvent($card, $dto);
        $listener = resolve(SendPaymentCardToOnecListeners::class);
        $listener->handle($event);

        $record = Request::first();

        $this->assertTrue($record->command->isAddPaymentCardToMember());
    }

    /** @test */
    public function success_delete()
    {
        /** @var $model Dealer */
        $model = $this->dealerBuilder->create();
        $card = $this->paymentCardBuilder->setData([
            'guid' => $this->faker->uuid
        ])->setMember($model)->create();

        $this->assertNull(Request::first());

        $event = new DeletePaymentCardFromMemberEvent($card);
        $listener = resolve(SendPaymentCardToOnecListeners::class);
        $listener->handle($event);

        $record = Request::first();

        $this->assertTrue($record->command->isDeletePaymentCardToMember());
    }

    /** @test */
    public function not_delete_if_not_guid()
    {
        /** @var $model Dealer */
        $model = $this->dealerBuilder->create();
        $card = $this->paymentCardBuilder->setMember($model)->create();

        $this->assertNull(Request::first());

        $event = new DeletePaymentCardFromMemberEvent($card);
        $listener = resolve(SendPaymentCardToOnecListeners::class);
        $listener->handle($event);

        $this->assertNull(Request::first());
    }

    /** @test */
    public function not_send_without_member_guid()
    {
        /** @var $model Dealer */
        $model = $this->dealerBuilder->setData([
            'guid' => null
        ])->create();
        $card = $this->paymentCardBuilder->setMember($model)->create();
        $dto = $this->dto();

        $this->assertNull(Request::first());

        $event = new AddPaymentCardToMemberEvent($card, $dto);
        $listener = resolve(SendPaymentCardToOnecListeners::class);
        $listener->handle($event);

        $this->assertNull(Request::first());
    }

    /** @test */
    public function something_wrong()
    {
        /** @var $model Dealer */
        $model = $this->dealerBuilder->create();
        $card = $this->paymentCardBuilder->setMember($model)->create();
        $dto = $this->dto();

        $this->mock(RequestService::class, function(MockInterface $mock){
            $mock->shouldReceive("addPaymentCard")
                ->andThrows(\Exception::class, "some exception message");
        });

        $event = new AddPaymentCardToMemberEvent($card, $dto);
        $listener = resolve(SendPaymentCardToOnecListeners::class);

        $this->expectException(TranslatedException::class);
        $this->expectExceptionMessage("some exception message");
        $this->expectExceptionCode(502);

        $listener->handle($event);
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
