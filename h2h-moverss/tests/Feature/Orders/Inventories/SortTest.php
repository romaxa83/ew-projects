<?php

namespace Tests\Feature\Orders\Inventories;

use App\Models\Order;
use App\Models\Order\Inventory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Divisions\DivisionBuilder;
use Tests\Builders\Orders\InventoryBuilder;
use Tests\Builders\Orders\OrderBuilder;
use Tests\TestCase;

class SortTest extends TestCase
{
    use DatabaseTransactions;

    protected OrderBuilder $orderBuilder;
    protected InventoryBuilder $inventoryBuilder;
    protected DivisionBuilder $divisionBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->inventoryBuilder = resolve(InventoryBuilder::class);
        $this->divisionBuilder = resolve(DivisionBuilder::class);
    }

    /** @test */
    public function it_can_sort_inventory_items()
    {
        $division = $this->divisionBuilder->misc([
            "tz" => "America/Chicago"
        ])->create();
        $this->session(['division' => $division->toArray()]);

        // Arrange
        $user = $this->loginUser();
        /** @var Order $order */
        $order = $this->orderBuilder->create();

        /** @var Inventory $inventory1 */
        $inventory1 = $this->inventoryBuilder
            ->order($order)
            ->setData([
                'is_section' => 0,
                'section_id' => 0,
                'price' => 100,
                'qty' => 2,
                'weight' => 10,
                'volume' => 5,
                'title' => 'Test Item 1',
                'sort' => 1,
            ])
            ->create();

        /** @var Inventory $inventory2 */
        $inventory2 = $this->inventoryBuilder
            ->order($order)
            ->setData([
                'is_section' => 0,
                'section_id' => 0,
                'price' => 150,
                'qty' => 3,
                'weight' => 15,
                'volume' => 7,
                'title' => 'Test Item 2',
                'sort' => 2,
            ])
            ->create();

        // Act - Change the sort order
        $response = $this->postJson(route('orders.inventory.sort', [
            'id' => $order->id
        ]), [
            'items' => [
                [
                    'id' => $inventory1->id,
                    'sort' => 2,
                    'section_id' => 0,
                ],
                [
                    'id' => $inventory2->id,
                    'sort' => 1,
                    'section_id' => 0,
                ],
            ]
        ]);

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'record' => [
                    'id' => $order->id,
                ]
            ]);

        // Check that the sort order was updated
        $inventory1->refresh();
        $inventory2->refresh();
        $this->assertEquals(2, $inventory1->sort);
        $this->assertEquals(1, $inventory2->sort);
    }

    /** @test */
    public function it_can_move_inventory_items_between_sections()
    {
        $division = $this->divisionBuilder->misc([
            "tz" => "America/Chicago"
        ])->create();
        $this->session(['division' => $division->toArray()]);

        // Arrange
        $user = $this->loginUser();
        /** @var Order $order */
        $order = $this->orderBuilder->create();

        /** @var Inventory $section1 */
        $section1 = $this->inventoryBuilder
            ->order($order)
            ->setData([
                'is_section' => 1,
                'section_id' => 0,
                'title' => 'Section 1',
                'sort' => 1,
            ])
            ->create();

        /** @var Inventory $section2 */
        $section2 = $this->inventoryBuilder
            ->order($order)
            ->setData([
                'is_section' => 1,
                'section_id' => 0,
                'title' => 'Section 2',
                'sort' => 2,
            ])
            ->create();

        /** @var Inventory $item1 */
        $item1 = $this->inventoryBuilder
            ->order($order)
            ->section_id($section1)
            ->setData([
                'is_section' => 0,
                'title' => 'Item in Section 1',
                'sort' => 1,
                'price' => 100,
                'qty' => 1,
            ])
            ->create();

        // Act - Move item from section1 to section2
        $response = $this->postJson(route('orders.inventory.sort', [
            'id' => $order->id
        ]), [
            'items' => [
                [
                    'id' => $item1->id,
                    'sort' => 1,
                    'section_id' => $section2->id,
                ],
            ]
        ]);

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'record' => [
                    'id' => $order->id,
                ]
            ]);

        // Check that the item was moved to section2
        $item1->refresh();
        $this->assertEquals($section2->id, $item1->section_id);
    }
}
