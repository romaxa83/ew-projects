<?php


namespace Tests\Helpers\Traits\Orders;


use App\Models\Orders\Order;
use Illuminate\Http\UploadedFile;

trait InspectionMethodsHelper
{
    private function sendVin(int $orderId, int $vehicleId, string $vin): void
    {
        $this->postJson(
            route(
                'mobile.orders.vehicles.inspect-vin',
                [
                    'order' => $orderId,
                    'vehicle' => $vehicleId,
                ]
            ),
            [
                'vin' => mb_convert_case($vin, MB_CASE_UPPER),
            ]
        )
            ->assertOk()
            ->assertJsonPath('data.pickup_inspection.has_vin_inspection', true);
    }

    private function sendDamage(int $orderId, int $vehicleId, int $type): void
    {
        if (!isset($this->inspections[$type])) {
            return;
        }

        $this->postJson(
            route('mobile.orders.vehicles.inspect-' . $this->inspections[$type] . '-damage', [$orderId, $vehicleId]),
            [
                Order::INSPECTION_DAMAGE_FIELD_NAME => UploadedFile::fake()->image('some_name.jpg'),
            ]
        )
            ->assertOk();
    }

    private function sendExterior(int $orderId, int $vehicleId, int $type): void
    {
        if (!isset($this->inspections[$type])) {
            return;
        }

        $timestamp = now()->subDays(2)->timestamp;

        $this->postJson(
            route('mobile.orders.vehicles.inspect-' . $this->inspections[$type] . '-exterior', [$orderId, $vehicleId]),
            [
                'photo_id' => 1,
                Order::INSPECTION_PHOTO_FIELD_NAME => UploadedFile::fake()->image('some_name.jpg'),
                'photo_lat' => 50.12345,
                'photo_lng' => 60.98765,
                'photo_timestamp' => $timestamp,
            ]
        )
            ->assertOk();
    }

    private function sendInterior(int $orderId, int $vehicleId, int $type): void
    {
        if (!isset($this->inspections[$type])) {
            return;
        }

        $this->postJson(
            route('mobile.orders.vehicles.inspect-' . $this->inspections[$type] . '-interior', [$orderId, $vehicleId]),
            [
                'odometer' => 1233,
            ]
        )
            ->assertOk();
    }

    private function sendSignature(int $orderId, int $type): void
    {
        if (!isset($this->inspections[$type])) {
            return;
        }

        $this->postJson(
            route('mobile.orders.' . $this->inspections[$type] . '-signature', $orderId),
            [
                'customer_full_name' => 'Customer full name',
                'driver_signature' => UploadedFile::fake()->image('driver_signature.jpg'),
                'customer_signature' => UploadedFile::fake()->image('customer_signature.jpg'),
            ]
        )
            ->assertOk();
    }

    private function sendPayment(int $orderId, int $amount): void
    {
        $this->postJson(
            route('v2.carrier-mobile.orders.add-payment-data', $orderId),
            [
                'driver_payment_amount' => $amount,
            ]
        )
            ->assertOk();
    }

    private function sendPaymentUship(int $orderId, string $code): void
    {
        $this->postJson(
            route('v2.carrier-mobile.orders.add-payment-data', $orderId),
            [
                'driver_payment_uship_code' => $code,
            ]
        )
            ->assertOk();
    }
}
