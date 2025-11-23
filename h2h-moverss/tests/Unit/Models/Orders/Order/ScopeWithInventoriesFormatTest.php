<?php

namespace Tests\Unit\Models\Orders\Order;

use App\Models\Order;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Orders\InventoryBuilder;
use Tests\Builders\Orders\OrderBuilder;
use Tests\TestCase;

class ScopeWithInventoriesFormatTest extends TestCase
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
    public function scopeWithInventoriesFormat_loads_inventories_with_no_section_id()
    {
        // Create an order
        /** @var Order $order */
        $order = $this->orderBuilder->create();

        // Add inventories with section_id = 0
        $this->inventoryBuilder
            ->order($order)
            ->setData([
                'is_section' => 1,
                'section_id' => 0,
                'title' => 'Section 1',
                'sort' => 1
            ])
            ->create();

        $this->inventoryBuilder
            ->order($order)
            ->setData([
                'is_section' => 1,
                'section_id' => 0,
                'title' => 'Section 2',
                'sort' => 2
            ])
            ->create();

        // Call the method
        $result = Order::withInventoriesFormat($order->id)->first();

        // Assert that inventories are loaded
        $this->assertNotNull($result->inventories);
        $this->assertEquals(2, $result->inventories->count());

        // Assert that inventories have section_id = 0
        foreach ($result->inventories as $inventory) {
            $this->assertEquals(0, $inventory->section_id);
        }

        // Assert that inventories are ordered by is_section desc and sort asc
        $this->assertEquals('Section 1', $result->inventories[0]->title);
        $this->assertEquals('Section 2', $result->inventories[1]->title);
    }

    /** @test */
    public function scopeWithInventoriesFormat_loads_children_for_each_inventory()
    {
        // Create an order
        /** @var Order $order */
        $order = $this->orderBuilder->create();

        // Add a section inventory
        /** @var Order\Inventory $section */
        $section = $this->inventoryBuilder
            ->order($order)
            ->setData([
                'is_section' => 1,
                'section_id' => 0,
                'title' => 'Section 1',
                'sort' => 1
            ])
            ->create();

        // Add child inventories to the section
        $this->inventoryBuilder
            ->order($order)
            ->section_id($section)
            ->setData([
                'is_section' => 0,
                'title' => 'Item 1',
                'sort' => 1,
                'qty' => 2,
                'volume' => 10,
                'weight' => 20
            ])
            ->create();

        $this->inventoryBuilder
            ->order($order)
            ->section_id($section)
            ->setData([
                'is_section' => 0,
                'title' => 'Item 2',
                'sort' => 2,
                'qty' => 3,
                'volume' => 5,
                'weight' => 10
            ])
            ->create();

        // Call the method
        $result = Order::withInventoriesFormat($order->id)->first();

        // Assert that the section inventory is loaded
        $this->assertNotNull($result->inventories);
        $this->assertEquals(1, $result->inventories->count());

        // Assert that the section has children
        $this->assertNotNull($result->inventories[0]->children);
        $this->assertEquals(2, $result->inventories[0]->children->count());

        // Assert that children are ordered by sort
        $this->assertEquals('Item 1', $result->inventories[0]->children[0]->title);
        $this->assertEquals('Item 2', $result->inventories[0]->children[1]->title);
    }

    /** @test */
    public function scopeWithInventoriesFormat_orders_inventories_by_is_section_desc_and_sort_asc()
    {
        // Create an order
        /** @var Order $order */
        $order = $this->orderBuilder->create();

        // Add inventories with different is_section values and sort orders
        $this->inventoryBuilder
            ->order($order)
            ->setData([
                'is_section' => 0, // Not a section
                'section_id' => 0,
                'title' => 'Item 1',
                'sort' => 1
            ])
            ->create();

        $this->inventoryBuilder
            ->order($order)
            ->setData([
                'is_section' => 1, // Is a section
                'section_id' => 0,
                'title' => 'Section 1',
                'sort' => 2
            ])
            ->create();

        $this->inventoryBuilder
            ->order($order)
            ->setData([
                'is_section' => 1, // Is a section
                'section_id' => 0,
                'title' => 'Section 2',
                'sort' => 1
            ])
            ->create();

        // Call the method
        $result = Order::withInventoriesFormat($order->id)->first();

        // Assert that inventories are loaded
        $this->assertNotNull($result->inventories);
        $this->assertEquals(3, $result->inventories->count());

        // Assert that inventories are ordered by is_section desc and sort asc
        // Sections should come first (is_section=1), ordered by sort
        $this->assertEquals('Section 2', $result->inventories[0]->title); // is_section=1, sort=1
        $this->assertEquals('Section 1', $result->inventories[1]->title); // is_section=1, sort=2
        $this->assertEquals('Item 1', $result->inventories[2]->title);    // is_section=0, sort=1
    }

    /** @test */
    public function scopeWithInventoriesFormat_only_loads_children_for_the_specified_order()
    {
        // Create two orders
        /** @var Order $order1 */
        $order1 = $this->orderBuilder->create();

        /** @var Order $order2 */
        $order2 = $this->orderBuilder->create();

        // Add a section inventory to order1
        /** @var Order\Inventory $section1 */
        $section1 = $this->inventoryBuilder
            ->order($order1)
            ->setData([
                'is_section' => 1,
                'section_id' => 0,
                'title' => 'Section 1 (Order 1)',
                'sort' => 1
            ])
            ->create();

        // Add child inventories to the section in order1
        $this->inventoryBuilder
            ->order($order1)
            ->section_id($section1)
            ->setData([
                'is_section' => 0,
                'title' => 'Item 1 (Order 1)',
                'sort' => 1
            ])
            ->create();

        // Add a section inventory to order2
        /** @var Order\Inventory $section2 */
        $section2 = $this->inventoryBuilder
            ->order($order2)
            ->setData([
                'is_section' => 1,
                'section_id' => 0,
                'title' => 'Section 1 (Order 2)',
                'sort' => 1
            ])
            ->create();

        // Add child inventories to the section in order2
        $this->inventoryBuilder
            ->order($order2)
            ->section_id($section2)
            ->setData([
                'is_section' => 0,
                'title' => 'Item 1 (Order 2)',
                'sort' => 1
            ])
            ->create();

        // Call the method for order1
        $result1 = Order::withInventoriesFormat($order1->id)->find($order1->id);

        // Assert that only children for order1 are loaded
        $this->assertNotNull($result1->inventories);
        $this->assertEquals(1, $result1->inventories->count());
        $this->assertEquals('Section 1 (Order 1)', $result1->inventories[0]->title);
        $this->assertEquals(1, $result1->inventories[0]->children->count());
        $this->assertEquals('Item 1 (Order 1)', $result1->inventories[0]->children[0]->title);

        // Call the method for order2
        $result2 = Order::withInventoriesFormat($order2->id)->find($order2->id);

        // Assert that only children for order2 are loaded
        $this->assertNotNull($result2->inventories);
        $this->assertEquals(1, $result2->inventories->count());
        $this->assertEquals('Section 1 (Order 2)', $result2->inventories[0]->title);
        $this->assertEquals(1, $result2->inventories[0]->children->count());
        $this->assertEquals('Item 1 (Order 2)', $result2->inventories[0]->children[0]->title);
    }
}
