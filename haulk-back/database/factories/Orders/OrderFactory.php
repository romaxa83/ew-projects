<?php

namespace Database\Factories\Orders;

use App\Models\Contacts\Contact;
use App\Models\Orders\Inspection;
use App\Models\Orders\Order;
use App\Models\Orders\Vehicle;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @method Order|Order[]|Collection create($attributes = [], ?Model $parent = null)
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'broker_id' => null,
            'carrier_id' => 1,
            'deleted_at' => null,
            'load_id' => $this->faker->sentence,
            'user_id' => User::factory()->dispatcher(),
            'driver_id' => null,
            'dispatcher_id' => null,
            'status' => Order::STATUS_NEW,
            'is_billed' => false,
            'deduct_from_driver' => false,
            'pickup_contact' => Contact::factory()
                ->make()
                ->toArray(),
            'delivery_contact' => Contact::factory()
                ->make()
                ->toArray(),
            'shipper_contact' => Contact::factory()
                ->make()
                ->toArray(),
            'has_pickup_inspection' => true,
            'has_delivery_inspection' => false,
            'public_token' => (new Order())->generatePublicToken(),
            'seen_by_driver' => false,
        ];
    }

    public function newStatus(): self
    {
        return $this->state(
            [
                'driver_id' => null,
                'dispatcher_id' => User::factory()->dispatcher(),
            ]
        );
    }

    public function assignedStatus(): self
    {
        return $this->state(
            [
                'driver_id' => User::factory()->driver(),
                'dispatcher_id' => User::factory()->dispatcher(),
            ]
        );
    }

    public function deletedStatus(): self
    {
        return $this
            ->assignedStatus()
            ->state(
                [
                    'deleted_at' => Carbon::now()
                ]
            );
    }

    public function pickedUpStatus(): self
    {
        return $this
            ->assignedStatus()
            ->state(
                [
                    'status' => Order::STATUS_PICKED_UP,
                    'pickup_date_actual' => Carbon::now()->getTimestamp()
                ]
            );
    }

    public function deliveredStatus(): self
    {
        return $this
            ->pickedUpStatus()
            ->state(
                [
                    'status' => Order::STATUS_DELIVERED,
                    'delivery_date_actual' => Carbon::now()->getTimestamp()
                ]
            );
    }

    public function reviewed(bool $reviewed = true): self
    {
        return $this->state(
            [
                'need_review' => true,
                'has_review' => $reviewed,
            ]
        );
    }

    public function pickupFullName(string $name): self
    {
        return $this->state(
            [
                'pickup_contact' => Contact::factory()
                    ->name($name)
                    ->make()
                    ->toArray()
            ]
        );
    }

    public function deliveryFullName(string $name): self
    {
        return $this->state(
            [
                'delivery_contact' => Contact::factory()
                    ->name($name)
                    ->make()
                    ->toArray()
            ]
        );
    }

    public function shipperFullName(string $name): self
    {
        return $this->state(
            [
                'shipper_contact' => Contact::factory()
                    ->name($name)
                    ->make()
                    ->toArray()
            ]
        );
    }

    public function pickupOverdue(): self
    {
        return $this
            ->assignedStatus()
            ->state(
                [
                    'pickup_date' => Carbon::now()->subDays(2)->getTimestamp(),
                    'pickup_date_actual' => null
                ]
            );
    }

    public function deliveryOverdue(): self
    {
        return $this
            ->pickedUpStatus()
            ->state(
                [
                    'delivery_date' => Carbon::now()->subDays(2)->getTimestamp(),
                    'delivery_date_actual' => null,
                ]
            );
    }

    public function hasPickupInspection(): self
    {
        return $this
            ->state(
                [
                    'has_pickup_inspection' => true
                ]
            )
            ->afterCreating(
                static function (Order $order): void {
                    if ($order->vehicles) {
                        $order
                            ->vehicles
                            ->each(
                                static function (Vehicle $vehicle): void {
                                    $vehicle->pickup_inspection_id = Inspection::factory()->create()->id;
                                    $vehicle->save();
                                }
                            );
                        return;
                    }
                    Vehicle::factory(['order_id' => $order->id])
                        ->has(
                            Inspection::factory(),
                            'pickupInspection'
                        )
                        ->create();
                }
            );
    }

    public function hasDeliveryInspection(): self
    {
        return $this
            ->state(
                [
                    'has_delivery_inspection' => true
                ]
            )
            ->afterCreating(
                static function (Order $order): void {
                    if ($order->vehicles) {
                        $order
                            ->vehicles
                            ->each(
                                static function (Vehicle $vehicle): void {
                                    $vehicle->delivery_inspection_id = Inspection::factory()->create()->id;
                                    $vehicle->save();
                                }
                            );
                        return;
                    }
                    Vehicle::factory(['order_id' => $order->id])
                        ->has(
                            Inspection::factory(),
                            'deliveryInspection'
                        )
                        ->create();
                }
            );
    }

    public function hasPickupSignature(): self
    {
        return $this
            ->state(
                [
                    'has_pickup_signature' => true
                ]
            );
    }

    public function hasDeliverySignature(): self
    {
        return $this
            ->state(
                [
                    'has_delivery_signature' => true
                ]
            );
    }
}
