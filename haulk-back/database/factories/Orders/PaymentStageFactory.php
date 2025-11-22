<?php

namespace Database\Factories\Orders;

use App\Models\Orders\Order;
use App\Models\Orders\Payment;
use App\Models\Orders\PaymentStage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PaymentStageFactory extends Factory
{
    protected $model = PaymentStage::class;

    public function definition(): array
    {
        return [
            'amount' => 1500,
            'payment_date' => now()->timestamp,
            'payer' => Payment::PAYER_BROKER,
            'method_id' => Payment::METHOD_ACH,
        ];
    }

    public function brokerFee(float $amount): self
    {
        return $this->state(
            [
                'payer' => Payment::PAYER_CARRIER,
                'amount' => $amount
            ]
        );
    }

    public function broker(float $amount): self
    {
        return $this->state(
            [
                'payer' => Payment::PAYER_BROKER,
                'amount' => $amount
            ]
        );
    }

    public function customer(float $amount): self
    {
        return $this->state(
            [
                'payer' => Payment::PAYER_CUSTOMER,
                'amount' => $amount
            ]
        );
    }

    /**
     * @param int|Order $order
     * @return self
     */
    public function order($order): self
    {
        return $this->state(
            [
                'order_id' => is_int($order) ? $order : $order->id
            ]
        );
    }

    public function referenceNumber(?string $referenceNumber = null): self
    {
        return $this->state(
            [
                'reference_number' => $referenceNumber ?? Str::random()
            ]
        );
    }
}
