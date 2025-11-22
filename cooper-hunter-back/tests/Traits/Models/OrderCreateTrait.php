<?php


namespace Tests\Traits\Models;


use App\Enums\Orders\OrderStatusEnum;
use App\Models\Catalog\Products\Product;
use App\Models\Catalog\Products\ProductSerialNumber;
use App\Models\Orders\Categories\OrderCategory;
use App\Models\Orders\Order;
use App\Models\Orders\OrderPayment;
use App\Models\Projects\Pivot\SystemUnitPivot;
use App\Models\Technicians\Technician;
use Database\Factories\Orders\OrderFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\WithFaker;

trait OrderCreateTrait
{
    use WithFaker;
    use ProjectCreateTrait;

    private int $countOrder = 1;
    private ?Technician $orderTechnician = null;
    private bool $withProject = false;
    private bool $withoutCreatedEvent = false;


    public function manyOrder(int $count = 1): static
    {
        $this->countOrder = $count;

        return $this;
    }

    public function setOrderTechnician(Technician $technician): static
    {
        $this->orderTechnician = $technician;

        return $this;
    }

    public function withProject(): static
    {
        $this->withProject = true;

        return $this;
    }

    public function withoutCreatedEvent(): static
    {
        $this->withoutCreatedEvent = true;

        return $this;
    }

    private function getFactory(string $status, array $attributes = []): OrderFactory
    {
        $orderTechnician = !$this->orderTechnician ? Technician::factory()
            ->certified()
            ->create() : $this->orderTechnician;

        if ($this->withProject) {
            $project = $this->createProjectForMember($orderTechnician);

            /**@var SystemUnitPivot $systemUnit */
            $systemUnit = $project->systems()
                ->first()
                ->units()
                ->first()->unit;

            $orderData = [
                'product_id' => $systemUnit->product_id,
                'serial_number' => $systemUnit->serial_number,
                'project_id' => $project->id
            ];
        } else {
            /**@var Product $product */
            $product = Product::factory()
                ->has(
                    ProductSerialNumber::factory()
                        ->state(
                            ['serial_number' => $this->faker->lexify]
                        ),
                    'serialNumbers'
                )
                ->create();

            $orderData = [
                'product_id' => $product->id,
                'serial_number' => $product->serialNumbers->first()->serial_number,
                'project_id' => null
            ];
        }

        $factory = Order::factory(
            array_merge(
                [
                    'status' => $status,
                    'technician_id' => $orderTechnician->id
                ],
                $orderData,
                $attributes
            )
        );

        if ($this->countOrder > 1) {
            $factory = $factory->count($this->countOrder);
        }

        return $factory;
    }

    public function createCreatedOrder(array $attributes = []): Order|Collection
    {
        $factory = $this->getFactory(OrderStatusEnum::CREATED, $attributes)
            ->has(
                OrderPayment::factory(
                    [
                        'order_price_with_discount' => null,
                        'order_price' => null,
                        'shipping_cost' => null,
                        'tax' => null,
                        'discount' => null
                    ]
                ),
                'payment'
            );

        return !$this->withoutCreatedEvent ? $factory->create() : $factory->createQuietly();
    }

    public function createPendingPaidOrder(array $attributes = []): Order|Collection
    {
        $factory = $this->getFactory(OrderStatusEnum::PENDING_PAID, $attributes)
            ->has(
                OrderPayment::factory(),
                'payment'
            );

        return !$this->withoutCreatedEvent ? $factory->create() : $factory->createQuietly();
    }

    public function createPaidOrder(array $attributes = []): Order|Collection
    {
        $factory = $this->getFactory(OrderStatusEnum::PAID, $attributes)
            ->has(
                OrderPayment::factory(['paid_at' => time() - 100]),
                'payment'
            );

        return !$this->withoutCreatedEvent ? $factory->create() : $factory->createQuietly();
    }

    public function createShippedOrder(array $attributes = []): Order|Collection
    {
        $factory = $this->getFactory(OrderStatusEnum::SHIPPED, $attributes)
            ->has(
                OrderPayment::factory(['paid_at' => time() - 100]),
                'payment'
            );

        $order = !$this->withoutCreatedEvent ? $factory->create() : $factory->createQuietly();

        if ($this->countOrder === 1) {
            $order->shipping->trk_number = (string)$this->faker->numberBetween(10000, 100000000);
            $order->shipping->save();
        } else {
            $order->each(
                function (Order $order)
                {
                    $order->shipping->trk_number = (string)$this->faker->numberBetween(10000, 100000000);
                    $order->shipping->save();
                }
            );
        }

        return $order;
    }

    public function createCanceledOrder(array $attributes = []): Order|Collection
    {
        $factory = $this->getFactory(OrderStatusEnum::CANCELED, $attributes)
            ->has(
                OrderPayment::factory(),
                'payment'
            );

        return !$this->withoutCreatedEvent ? $factory->create() : $factory->createQuietly();
    }

    public function createAllStatusesOrder(): array
    {
        return [
            OrderStatusEnum::CREATED => $this->createCreatedOrder(),
            OrderStatusEnum::PENDING_PAID => $this->createPendingPaidOrder(),
            OrderStatusEnum::PAID => $this->createPaidOrder(),
            OrderStatusEnum::SHIPPED => $this->createShippedOrder(),
            OrderStatusEnum::CANCELED => $this->createCanceledOrder(),
        ];
    }

    public function createOrderCategories(): Collection
    {
        return OrderCategory::factory()
            ->count(2)
            ->create();
    }
}
