<?php

namespace Database\Factories\Orders;

use App\Models\Orders\Bonus;
use App\Models\Orders\Expense;
use App\Models\Orders\Order;
use App\Models\Orders\Payment;
use App\Models\Orders\PaymentStage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    private function getTotal(): int
    {
        return $this->faker->numberBetween(50, 10000);
    }

    public function definition(): array
    {
        return [
            'total_carrier_amount' => $total = $this->getTotal(),

            'customer_payment_amount' => $total,
            'customer_payment_method_id' => Payment::METHOD_USHIP,
            'customer_payment_location' => Order::LOCATION_PICKUP,

            'broker_payment_amount' => null,
            'broker_payment_method_id' => null,
            'broker_payment_days' => null,
            'broker_payment_begins' => null,

            'broker_fee_amount' => null,
            'broker_fee_method_id' => null,
            'broker_fee_days' => null,
            'broker_fee_begins' => null,

            'invoice_issue_date' => fn() => now()->timestamp,
            'driver_payment_data_sent' => false,
        ];
    }

    public function broker(?int $amount = null, ?int $method = null, ?int $days = null, ?string $begins = null): self
    {
        foreach ($this->states as $state) {
            $state = $state();
            if (array_key_exists('customer_payment_amount', $state)) {
                $customerPaymentAmount = $state['customer_payment_amount'];
                break;
            }
        }
        $brokerPaymentAmount = $total = $amount ?? $this->getTotal();
        if (!empty($customerPaymentAmount)) {
            $total += $customerPaymentAmount;
        }
        $days = $days ?? mt_rand(1, 10);
        return $this
            ->state(
                [
                    'total_carrier_amount' => $total,
                    'broker_payment_amount' => $brokerPaymentAmount,
                    'broker_payment_method_id' => $method ?? array_rand(Payment::BROKER_METHODS),
                    'broker_payment_days' => $days,
                    'broker_payment_begins' => $begins ?? array_rand(Order::TERMS_BEGINS),
                    'broker_payment_planned_date' => Carbon::now()->addBusinessDays($days)->getTimestamp(),
                    'broker_fee_amount' => null,
                    'broker_fee_method_id' => null,
                    'broker_fee_days' => null,
                    'broker_fee_begins' => null,
                ]
            );
    }

    public function customer(?int $amount = null, ?int $method = null, ?string $begins = null): self
    {
        foreach ($this->states as $state) {
            $state = $state();
            if (array_key_exists('broker_payment_amount', $state)) {
                $brokerPaymentAmount = $state['broker_payment_amount'];
                break;
            }
        }
        $customerPaymentAmount = $total = $amount ?? $this->getTotal();
        if (!empty($brokerPaymentAmount)) {
            $total += $brokerPaymentAmount;
        }
        return $this
            ->state(
                [
                    'total_carrier_amount' => $total,
                    'customer_payment_amount' => $customerPaymentAmount,
                    'customer_payment_method_id' => $method ?? array_rand(Payment::CUSTOMER_METHODS),
                    'customer_payment_location' => $begins ?? array_rand(Order::LOCATIONS),
                    'customer_payment_planned_date' => Carbon::now()->addDay()->getTimestamp()
                ]
            );
    }

    public function brokerFee(?int $method = null, ?int $days = null, ?string $begins = null): self
    {
        foreach ($this->states as $state) {
            $state = $state();
            if (array_key_exists('customer_payment_amount', $state)) {
                $customerPaymentAmount = $state['customer_payment_amount'];
                break;
            }
        }
        if (empty($customerPaymentAmount)) {
            return $this
                ->customer()
                ->brokerFee();
        }
        $total = ceil($customerPaymentAmount * 0.8);
        $days = $days ?? mt_rand(1, 10);
        return $this->state(
            [
                'total_carrier_amount' => $total,
                'broker_fee_amount' => $customerPaymentAmount - $total,
                'broker_fee_method_id' => $method ?? array_rand(Payment::CARRIER_METHODS),
                'broker_fee_days' => $days,
                'broker_fee_begins' => $begins ?? array_rand(Order::TERMS_BEGINS),
                'broker_fee_planned_date' => Carbon::now()->addBusinessDays($days)->getTimestamp(),
                'broker_payment_amount' => null,
                'broker_payment_method_id' => null,
                'broker_payment_days' => null,
                'broker_payment_begins' => null
            ]
        );
    }

    public function brokerOverdue(): self
    {
        return $this
            ->state(
                [
                    'broker_payment_planned_date' => Carbon::now()->subDay()->getTimestamp()
                ]
            );
    }

    public function customerOverdue(): self
    {
        return $this
            ->state(
                [
                    'customer_payment_planned_date' => Carbon::now()->subDay()->getTimestamp()
                ]
            );
    }

    public function brokerFeeOverdue(): self
    {
        return $this
            ->state(
                [
                    'broker_fee_planned_date' => Carbon::now()->subDay()->getTimestamp()
                ]
            );
    }

    public function brokerInvoice(?string $invoiceId = null): self
    {
        return $this->state(
            [
                'broker_payment_invoice_id' => $invoiceId ?? Str::random(10)
            ]
        );
    }

    public function customerInvoice(?string $invoiceId = null): self
    {
        return $this->state(
            [
                'customer_payment_invoice_id' => $invoiceId ?? Str::random(10)
            ]
        );
    }

    public function paid(): self
    {
        return $this
            ->configure()
            ->afterCreating(
                static function (Payment $payment): void {
                    $order = $payment->order;
                    $broker = 0.0;
                    $customer = 0.0;
                    $order
                        ->expenses
                        ->each(
                            static function (Expense $expense) use (&$broker, &$customer) {
                                if ($expense->to === Payment::PAYER_CUSTOMER) {
                                    $customer += $expense->price;
                                    return;
                                }
                                $broker += $expense->price;
                            }
                        );
                    $order
                        ->bonuses
                        ->each(
                            static function (Bonus $bonus) use (&$broker, &$customer) {
                                if ($bonus->to === Payment::PAYER_CUSTOMER) {
                                    $customer += $bonus->price;
                                    return;
                                }
                                $broker += $bonus->price;
                            }
                        );
                    if ($payment->broker_payment_amount) {
                        $broker += $payment->broker_payment_amount;
                    }
                    if ($payment->customer_payment_amount) {
                        $customer += $payment->customer_payment_amount;
                    }

                    if (!empty($broker)) {
                        PaymentStage::factory()->order($order)->broker($broker)->create();
                    }
                    if (!empty($customer)) {
                        PaymentStage::factory()->order($order)->customer($customer)->create();
                    }
                }
            );
    }

    public function brokerFeePaid(): self
    {
        return $this
            ->configure()
            ->afterCreating(
                static function (Payment $payment): void {
                    PaymentStage::factory()
                        ->order($payment->order)
                        ->brokerFee($payment->broker_fee_amount)
                        ->create();
                }
            );
    }

    public function driverPaymentDataSent(): self
    {
        return $this->state(
            [
                'driver_payment_data_sent' => true
            ]
        );
    }
}
