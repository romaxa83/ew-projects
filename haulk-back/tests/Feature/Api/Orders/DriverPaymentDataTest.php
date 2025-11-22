<?php


namespace Tests\Feature\Api\Orders;


use App\Broadcasting\Events\Orders\UpdateOrderBroadcast;
use App\Events\ModelChanged;
use App\Events\Orders\OrderUpdateEvent;
use App\Models\Orders\Order;
use App\Models\Orders\Payment;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Tests\Helpers\Traits\OrderFactoryHelper;
use Tests\TestCase;

class DriverPaymentDataTest extends TestCase
{
    use DatabaseTransactions;
    use OrderFactoryHelper;

    public function test_driver_payment_data_accepted_for_non_cop_cod(): void
    {
        $driver = $this->loginAsCarrierDriver();

        $order = $this->orderFactory(
            [
                'driver_id' => $driver->id
            ]
        );

        $this->createOrderPayment($order->id, 1234, Payment::METHOD_PAYPAL);

        // check if order visible
        $this->getJson(route('order-mobile.show', $order))
            ->assertOk();

        // post some correct data
        $this->postJson(
            route('v2.carrier-mobile.orders.add-payment-data', $order),
            [
                'driver_payment_comment' => 'some comment',
            ]
        )
            ->assertOk();
    }

    public function test_driver_payment_data_cop_not_received(): void
    {
        $driver = $this->loginAsCarrierDriver();

        $order = $this->orderFactory(
            [
                'driver_id' => $driver->id
            ]
        );

        $payment = $this->createOrderPayment($order->id, 1234, Payment::METHOD_CASH);

        // check if order visible
        $this->getJson(route('order-mobile.show', $order))
            ->assertOk();

        // send not received
        $testComment = 'test comment';

        Event::fake([
            ModelChanged::class,
            OrderUpdateEvent::class,
            UpdateOrderBroadcast::class
        ]);

        $this->postJson(
            route('v2.carrier-mobile.orders.add-payment-data', $order),
            [
                'driver_payment_comment' => $testComment,
            ]
        )
            ->assertOk();

        Event::assertDispatched(ModelChanged::class, 1);
        Event::assertDispatched(OrderUpdateEvent::class, 1);
        Event::assertDispatched(UpdateOrderBroadcast::class, 1);

        $payment->refresh();

        $this->assertEquals(
            $testComment,
            $payment->driver_payment_comment
        );

        $this->assertNull($payment->driver_payment_timestamp);

        $this->assertTrue($payment->driver_payment_data_sent);
    }

    public function test_driver_payment_data_cop_received(): void
    {
        $driver = $this->loginAsCarrierDriver();

        $order = $this->orderFactory(
            [
                'driver_id' => $driver->id
            ]
        );

        $amount = 1234;
        $payment = $this->createOrderPayment($order->id, $amount, Payment::METHOD_CASH);

        // check if order visible
        $this->getJson(route('order-mobile.show', $order))
            ->assertOk();

        // send cop received
        $this->postJson(
            route('v2.carrier-mobile.orders.add-payment-data', $order),
            [
                'driver_payment_amount' => $amount,
            ]
        )
            ->assertOk();

        $payment->refresh();

        $this->assertNull(
            $payment->driver_payment_method_id
        );

        $this->assertEquals(
            $amount,
            $payment->driver_payment_amount
        );

        $this->assertNull($payment->driver_payment_timestamp);

        $this->assertTrue($payment->driver_payment_data_sent);
    }

    public function test_driver_payment_data_cop_uship_received(): void
    {
        $driver = $this->loginAsCarrierDriver();

        $order = $this->orderFactory(
            [
                'driver_id' => $driver->id
            ]
        );

        $payment = $this->createOrderPayment($order->id, 1234, Payment::METHOD_CASH);

        // check if order visible
        $this->getJson(route('order-mobile.show', $order))
            ->assertOk();

        // send uship received
        $ushipCode = '1234asdf';

        $this->postJson(
            route('v2.carrier-mobile.orders.add-payment-data', $order),
            [
                'driver_payment_uship_code' => $ushipCode,
            ]
        )
            ->assertOk();

        $payment->refresh();

        $this->assertEquals(
            $ushipCode,
            $payment->driver_payment_uship_code
        );

        $this->assertNull($payment->driver_payment_timestamp);

        $this->assertTrue($payment->driver_payment_data_sent);
    }

    public function test_driver_payment_data_cop_check_received(): void
    {
        $driver = $this->loginAsCarrierDriver();

        $order = $this->orderFactory(
            [
                'driver_id' => $driver->id
            ]
        );

        $payment = $this->createOrderPayment($order->id, 1234, Payment::METHOD_CASH);

        // check if order visible
        $this->getJson(route('order-mobile.show', $order))
            ->assertOk();

        // send with no check photo
        $this->postJson(
            route('v2.carrier-mobile.orders.add-payment-data', $order),
            [
            ]
        )
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        // send with check photo
        $checkPhoto = UploadedFile::fake()->create('check.pdf');

        $this->postJson(
            route('v2.carrier-mobile.orders.add-payment-data', $order),
            [
                Order::DRIVER_PAYMENT_FIELD_NAME => $checkPhoto,
            ]
        )->assertOk();

        // send with check photo
        $checkPhoto = UploadedFile::fake()->create('check.jpg');

        $this->postJson(
            route('v2.carrier-mobile.orders.add-payment-data', $order),
            [
                Order::DRIVER_PAYMENT_FIELD_NAME => $checkPhoto,
            ]
        )->assertOk();

        // send with check photo
        $checkPhoto = UploadedFile::fake()->create('check.png');

        $this->postJson(
            route('v2.carrier-mobile.orders.add-payment-data', $order),
            [
                Order::DRIVER_PAYMENT_FIELD_NAME => $checkPhoto,
            ]
        )->assertOk();

        $payment->refresh();

        $this->assertNotEmpty(
            $payment->getFirstMedia(Order::DRIVER_PAYMENT_COLLECTION_NAME)
        );

        $this->assertNull($payment->driver_payment_timestamp);

        $this->assertTrue($payment->driver_payment_data_sent);
    }

    public function test_driver_payment_data_cop_paypal_received(): void
    {
        $driver = $this->loginAsCarrierDriver();

        $order = $this->orderFactory(
            [
                'driver_id' => $driver->id
            ]
        );

        $amount = 1234;
        $payment = $this->createOrderPayment($order->id, $amount, Payment::METHOD_CASH);

        // check if order visible
        $this->getJson(route('order-mobile.show', $order))
            ->assertOk();

        // send cop received
        $this->postJson(
            route('v2.carrier-mobile.orders.add-payment-data', $order),
            [
                'driver_payment_method_id' => Payment::METHOD_PAYPAL,
                'driver_payment_amount' => $amount,
            ]
        )
            ->assertOk();

        $payment->refresh();

        $this->assertEquals(
            Payment::METHOD_PAYPAL,
            $payment->driver_payment_method_id
        );

        $this->assertEquals(
            $amount,
            $payment->driver_payment_amount
        );

        $this->assertNotNull($payment->driver_payment_timestamp);

        $this->assertTrue($payment->driver_payment_data_sent);
    }

    public function test_account_type_needed_for_zelle_and_cashapp(): void
    {
        $driver = $this->loginAsCarrierDriver();

        $order = $this->orderFactory(
            [
                'driver_id' => $driver->id
            ]
        );

        $amount = 1234;
        $payment = $this->createOrderPayment($order->id, $amount, Payment::METHOD_CASH);

        // check if order visible
        $this->getJson(route('order-mobile.show', $order))
            ->assertOk();

        // send cop received with no account type
        $this->postJson(
            route('v2.carrier-mobile.orders.add-payment-data', $order),
            [
                'driver_payment_method_id' => Payment::METHOD_CASHAPP,
                'driver_payment_amount' => $amount,
            ]
        )
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        // send cop received
        $this->postJson(
            route('v2.carrier-mobile.orders.add-payment-data', $order),
            [
                'driver_payment_method_id' => Payment::METHOD_CASHAPP,
                'driver_payment_amount' => $amount,
                'driver_payment_account_type' => 'company',
            ]
        )
            ->assertOk();

        $payment->refresh();

        $this->assertEquals(
            Payment::METHOD_CASHAPP,
            $payment->driver_payment_method_id
        );

        $this->assertEquals(
            $amount,
            $payment->driver_payment_amount
        );

        $this->assertEquals(
            'company',
            $payment->driver_payment_account_type
        );

        $this->assertNotNull($payment->driver_payment_timestamp);

        $this->assertTrue($payment->driver_payment_data_sent);
    }
}
