<?php

namespace Database\Factories\Orders;

use App\Enums\Orders\OrderStatusEnum;
use App\Models\Orders\Categories\OrderCategory;
use App\Models\Orders\Order;
use App\Models\Orders\OrderShipping;
use App\Models\Projects\Project;
use App\Models\Technicians\Technician;
use App\ValueObjects\Phone;
use Database\Factories\BaseFactory;
use Illuminate\Database\Eloquent\Collection;

/**
 * @method Collection|Order[]|Order create(array $attributes = [])
 */
class OrderFactory extends BaseFactory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'technician_id' => $factory = Technician::factory(),
            'project_id' => Project::factory()->forTechnician($factory),
            'status' => OrderStatusEnum::CREATED,
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'phone' => new Phone($this->faker->phoneNumber),
        ];
    }

    public function paid(): self
    {
        return $this->status(OrderStatusEnum::PAID());
    }

    public function status(OrderStatusEnum $status): self
    {
        return $this->state(
            [
                'status' => $status->value,
            ]
        );
    }

    public function configure(): self
    {
        $categories = OrderCategory::factory()->count(2)->create();

        return $this->afterCreating(
            fn(Order $order) => $order->parts()->createMany([
                [
                    'order_category_id' => $categories[0]->id
                ],
                [
                    'order_category_id' => $categories[1]->id
                ]
            ])
        )->afterCreating(
            fn(Order $order) => OrderShipping::factory(['order_id' => $order->id])->create()
        );
    }
}
