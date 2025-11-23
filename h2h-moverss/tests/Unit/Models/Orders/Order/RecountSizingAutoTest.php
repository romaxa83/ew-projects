<?php

namespace Tests\Unit\Models\Orders\Order;

use App\Models\Order;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Orders\InventoryBuilder;
use Tests\Builders\Orders\OrderBuilder;
use Tests\TestCase;

class RecountSizingAutoTest extends TestCase
{
    use DatabaseTransactions;

    protected OrderBuilder $orderBuilder;
    protected InventoryBuilder $inventoryBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->inventoryBuilder = resolve(InventoryBuilder::class);
    }

    /** @test */
    public function recountSizingAuto_with_empty_inventories_sets_zero_values()
    {
        // Create an order with no inventories
        /** @var $order Order */
        $order = $this->orderBuilder->create();

        // Call the method
        $order->recountSizingAuto();

        // Assert that sizing values are zero
        $this->assertEquals(0, $order->sizing_volume);
        $this->assertEquals(0, $order->sizing_weight);
    }

    /** @test */
    public function recountSizingAuto_skips_section_inventories()
    {
        // Create an order
        /** @var $order Order */
        $order = $this->orderBuilder->create();

        // Add a section inventory
        $this->inventoryBuilder
            ->order($order)
            ->setData([
                'is_section' => true,
                'volume' => 10,
                'weight' => 20,
                'qty' => 2
            ])
            ->create();

        // Call the method
        $order->recountSizingAuto();

        // Assert that sizing values are zero (section should be skipped)
        $this->assertEquals(0, $order->sizing_volume);
        $this->assertEquals(0, $order->sizing_weight);
    }

    /** @test */
    public function recountSizingAuto_calculates_volume_and_weight_correctly()
    {
        // Create an order
        /** @var $order Order */
        $order = $this->orderBuilder->create();

        // Add non-section inventories with volume and weight
        $this->inventoryBuilder
            ->order($order)
            ->setData([
                'is_section' => false,
                'volume' => 10,
                'weight' => 20,
                'qty' => 2
            ])
            ->create();

        $this->inventoryBuilder
            ->order($order)
            ->setData([
                'is_section' => false,
                'volume' => 5,
                'weight' => 10,
                'qty' => 3
            ])
            ->create();

        // Call the method
        $order->recountSizingAuto();

        // Calculate expected values:
        // First inventory: volume = 10 * 2 = 20, weight = 20 * 2 = 40
        // Second inventory: volume = 5 * 3 = 15, weight = 10 * 3 = 30
        // Total: volume = 20 + 15 = 35, weight = 40 + 30 = 70

        // Assert that sizing values are calculated correctly
        $this->assertEquals(35, $order->sizing_volume);
        $this->assertEquals(70, $order->sizing_weight);
    }

    /** @test */
    public function recountSizingAuto_with_mixed_inventories_skips_sections()
    {
        // Create an order
        /** @var $order Order */
        $order = $this->orderBuilder->create();

        // Add a section inventory (should be skipped)
        $this->inventoryBuilder
            ->order($order)
            ->setData([
                'is_section' => true,
                'volume' => 100,
                'weight' => 200,
                'qty' => 1
            ])
            ->create();

        // Add non-section inventories
        $this->inventoryBuilder
            ->order($order)
            ->setData([
                'is_section' => false,
                'volume' => 10,
                'weight' => 20,
                'qty' => 2
            ])
            ->create();

        $this->inventoryBuilder
            ->order($order)
            ->setData([
                'is_section' => false,
                'volume' => 5,
                'weight' => 10,
                'qty' => 3
            ])
            ->create();

        // Call the method
        $order->recountSizingAuto();

        // Calculate expected values (section should be skipped):
        // First non-section inventory: volume = 10 * 2 = 20, weight = 20 * 2 = 40
        // Second non-section inventory: volume = 5 * 3 = 15, weight = 10 * 3 = 30
        // Total: volume = 20 + 15 = 35, weight = 40 + 30 = 70

        // Assert that sizing values are calculated correctly
        $this->assertEquals(35, $order->sizing_volume);
        $this->assertEquals(70, $order->sizing_weight);
    }
}
