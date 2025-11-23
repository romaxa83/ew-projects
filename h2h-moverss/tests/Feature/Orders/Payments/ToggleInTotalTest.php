<?php

namespace Tests\Feature\Orders\Payments;

use App\Models\Audit;
use App\Models\Division;
use App\Models\Order;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Divisions\DivisionBuilder;
use Tests\Builders\Orders\OrderBuilder;
use Tests\Builders\Orders\PaymentBuilder;
use Tests\TestCase;

class ToggleInTotalTest extends TestCase
{
    use DatabaseTransactions;

    protected DivisionBuilder $divisionBuilder;
    protected OrderBuilder $orderBuilder;
    protected PaymentBuilder $paymentBuilder;

    public function setUp(): void
    {
        $this->divisionBuilder = resolve(DivisionBuilder::class);
        $this->paymentBuilder = resolve(PaymentBuilder::class);
        $this->orderBuilder = resolve(OrderBuilder::class);

        parent::setUp();
    }

    /** @test */
    public function toggle_to_true_and_check_audit()
    {
        $user = $this->loginUser();
        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        /** @var $order Order */
        $order = $this->orderBuilder->create();
        /** @var $payment Order\Payment */
        $payment = $this->paymentBuilder
            ->order($order)
            ->in_total_sum(false)
            ->create();

        $data = [
            'order_id' => $order->id,
            'payment_id' => $payment->id,
        ];

        $this->post(route('orders.payments.toggleInTotal'), $data)
            ->assertJsonStructure([
                'records' => [
                    [
                        'id',
                        'user_id',
                        'order_id',
                        'payment_account_id',
                        'description',
                        'amount',
                        'in_total_sum',
                        'created_at',
                        'updated_at',
                        'account' => [
                            'id',
                            'title'
                        ]
                    ]
                ]
            ])
            ->assertJson([
                'success' => true,
                'records' => [
                    [
                        'id' => $payment->id,
                        'in_total_sum' => true,
                    ]
                ]
            ])
        ;

        $audit = Audit::query()
            ->where([
                'auditable_type' => Order\Payment::MORPH_NAME,
                'auditable_id' => $payment->id,
            ])
            ->where('event', Audit::EVENT_UPDATED)
            ->first();

        $this->assertEquals($audit->order_id, $order->id);
        $this->assertTrue($audit->new_values['in_total_sum']);
        $this->assertEquals($audit->old_values['in_total_sum'], 0);
    }

    /** @test */
    public function toggle_to_false_and_check_audit()
    {
        $user = $this->loginUser();
        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        /** @var $order Order */
        $order = $this->orderBuilder->create();
        /** @var $payment Order\Payment */
        $payment = $this->paymentBuilder
            ->order($order)
            ->in_total_sum(true)
            ->create();

        $data = [
            'order_id' => $order->id,
            'payment_id' => $payment->id,
        ];

        $this->post(route('orders.payments.toggleInTotal'), $data)
            ->assertJson([
                'success' => true,
                'records' => [
                    [
                        'id' => $payment->id,
                        'in_total_sum' => false,
                    ]
                ]
            ])
        ;

        $audit = Audit::query()
            ->where([
                'auditable_type' => Order\Payment::MORPH_NAME,
                'auditable_id' => $payment->id,
            ])
            ->where('event', Audit::EVENT_UPDATED)
            ->first();

        $this->assertEquals($audit->order_id, $order->id);
        $this->assertFalse($audit->new_values['in_total_sum']);
        $this->assertEquals($audit->old_values['in_total_sum'], 1);
    }
}
