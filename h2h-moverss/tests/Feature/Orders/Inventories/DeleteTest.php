<?php

namespace Tests\Feature\Orders\Inventories;

use App\Models\Communications\CommunicationRecord;
use App\Models\Order;
use App\Models\Order\Inventory;
use App\Models\Order\InventoryActivity;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Divisions\DivisionBuilder;
use Tests\Builders\Orders\InventoryBuilder;
use Tests\Builders\Orders\OrderBuilder;
use Tests\TestCase;

class DeleteTest extends TestCase
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
    public function it_can_delete_inventory_from_order()
    {
        $division = $this->divisionBuilder->misc([
            "tz" => "America/Chicago"
        ])->create();
        $this->session(['division' => $division->toArray()]);

        // Arrange
        $user = $this->loginUser();
        /** @var $order Order */
        $order = $this->orderBuilder->create();

        /** @var $inventory Inventory */
        $inventory = $this->inventoryBuilder
            ->order($order)
            ->setData([
                'is_section' => 0,
                'section_id' => 0,
                'price' => 100,
                'qty' => 2,
                'weight' => 10,
                'volume' => 5,
                'title' => 'Test Item',
                'sort' => 1,
            ])
            ->create();

        // Act
        $response = $this->deleteJson(route('orders.inventory.delete', [
            'orderId' => $order->id,
            'inventoryId' => $inventory->id
        ]));

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'record' => [
                    'id' => $order->id,
                ]
            ]);

        // Check that the inventory was deleted
        $this->assertDatabaseMissing(Inventory::TABLE, [
            'id' => $inventory->id,
        ]);
    }

    /** @test */
    public function it_deletes_child_inventories_when_deleting_a_section()
    {
        $division = $this->divisionBuilder->misc([
            "tz" => "America/Chicago"
        ])->create();
        $this->session(['division' => $division->toArray()]);

        // Arrange
        $user = $this->loginUser();
        /** @var $order Order */
        $order = $this->orderBuilder->create();

        /** @var $section Inventory */
        $section = $this->inventoryBuilder
            ->order($order)
            ->setData([
                'is_section' => 1,
                'section_id' => 0,
                'title' => 'Test Section',
                'sort' => 1,
            ])
            ->create();

        /** @var $childItem1 Inventory */
        $childItem1 = $this->inventoryBuilder
            ->order($order)
            ->section_id($section)
            ->setData([
                'is_section' => 0,
                'title' => 'Child Item 1',
                'sort' => 1,
                'qty' => 2,
                'price' => 100,
            ])
            ->create();

        /** @var $childItem2 Inventory */
        $childItem2 = $this->inventoryBuilder
            ->order($order)
            ->section_id($section)
            ->setData([
                'is_section' => 0,
                'title' => 'Child Item 2',
                'sort' => 2,
                'qty' => 3,
                'price' => 150,
            ])
            ->create();

        // Act
        $response = $this->deleteJson(route('orders.inventory.delete', [
            'orderId' => $order->id,
            'inventoryId' => $section->id
        ]));

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'record' => [
                    'id' => $order->id,
                ]
            ]);

        // Check that the section and all child items were deleted
        $this->assertDatabaseMissing(Inventory::TABLE, [
            'id' => $section->id,
        ]);
        $this->assertDatabaseMissing(Inventory::TABLE, [
            'id' => $childItem1->id,
        ]);
        $this->assertDatabaseMissing(Inventory::TABLE, [
            'id' => $childItem2->id,
        ]);
    }

    /** @test */
    public function it_creates_inventory_activity_record_when_deleting_inventory()
    {
        $division = $this->divisionBuilder->misc([
            "tz" => "America/Chicago"
        ])->create();
        $this->session(['division' => $division->toArray()]);

        // Arrange
        $user = $this->loginUser();
        /** @var $order Order */
        $order = $this->orderBuilder->create();

        /** @var $inventory Inventory */
        $inventory = $this->inventoryBuilder
            ->order($order)
            ->setData([
                'is_section' => 0,
                'section_id' => 0,
                'price' => 100,
                'qty' => 2,
                'weight' => 10,
                'volume' => 5,
                'title' => 'Test Item',
                'sort' => 1,
            ])
            ->create();

        // Act
        $response = $this->deleteJson(route('orders.inventory.delete', [
            'orderId' => $order->id,
            'inventoryId' => $inventory->id
        ]));

        // Assert
        $response->assertStatus(200);

        // Check that an InventoryActivity record was created
        $this->assertDatabaseHas(InventoryActivity::TABLE, [
            'order_id' => $order->id,
            'client_id' => $order->client_id,
            'action' => 'delete',
        ]);

        // Get the activity record
        $activity = InventoryActivity::where('order_id', $order->id)
            ->where('action', 'delete')
            ->first();

        // Check that the activity record has the correct miscs data
        $this->assertEquals($inventory->id, $activity->miscs['inventory_id']);
        $this->assertEquals('Test Item', $activity->miscs['title']);
        $this->assertEquals(2, $activity->miscs['qty']);
    }

    /** @test */
    public function it_creates_communication_record_when_deleting_inventory()
    {
        $division = $this->divisionBuilder->misc([
            "tz" => "America/Chicago"
        ])->create();
        $this->session(['division' => $division->toArray()]);

        // Arrange
        $user = $this->loginUser();
        /** @var $order Order */
        $order = $this->orderBuilder->create();

        /** @var $inventory Inventory */
        $inventory = $this->inventoryBuilder
            ->order($order)
            ->setData([
                'is_section' => 0,
                'section_id' => 0,
                'price' => 100,
                'qty' => 2,
                'weight' => 10,
                'volume' => 5,
                'title' => 'Test Item',
                'sort' => 1,
            ])
            ->create();

        // Act
        $response = $this->deleteJson(route('orders.inventory.delete', [
            'orderId' => $order->id,
            'inventoryId' => $inventory->id
        ]));

        // Assert
        $response->assertStatus(200);

        // Get the activity record
        $activity = InventoryActivity::where('order_id', $order->id)
            ->where('action', 'delete')
            ->first();

        // Check that a CommunicationRecord was created
        $this->assertDatabaseHas(CommunicationRecord::TABLE, [
            'entity_id' => $activity->id,
            'entity_type' => InventoryActivity::MORPH_NAME,
            'client_id' => $order->client_id,
            'order_id' => $order->id,
            'is_answered' => 1,
        ]);
    }

    /** @test */
    public function it_recalculates_sizing_when_deleting_inventory_from_order_with_auto_sizing()
    {
        $division = $this->divisionBuilder->misc([
            "tz" => "America/Chicago"
        ])->create();
        $this->session(['division' => $division->toArray()]);

        // Arrange
        $user = $this->loginUser();
        /** @var $order Order */
        $order = $this->orderBuilder
            ->sizing_is_auto(true)
            ->create();

        /** @var $inventory1 Inventory */
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

        /** @var $inventory2 Inventory */
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

        // Calculate initial sizing
        $order->recountSizingAuto();
        $initialVolume = $order->sizing_volume;
        $initialWeight = $order->sizing_weight;

        // Act
        $response = $this->deleteJson(route('orders.inventory.delete', [
            'orderId' => $order->id,
            'inventoryId' => $inventory1->id
        ]));

        // Assert
        $response->assertStatus(200);

        // Refresh the order
        $order->refresh();

        // Calculate expected values:
        // Initial: volume = (5 * 2) + (7 * 3) = 10 + 21 = 31, weight = (10 * 2) + (15 * 3) = 20 + 45 = 65
        // After deletion: volume = 7 * 3 = 21, weight = 15 * 3 = 45

        // Check that sizing values were recalculated
        $this->assertEquals(21, $order->sizing_volume);
        $this->assertEquals(45, $order->sizing_weight);
    }

    /** @test */
    public function it_does_not_recalculate_sizing_when_deleting_inventory_from_order_without_auto_sizing()
    {
        $division = $this->divisionBuilder->misc([
            "tz" => "America/Chicago"
        ])->create();
        $this->session(['division' => $division->toArray()]);

        // Arrange
        $user = $this->loginUser();
        /** @var $order Order */
        $order = $this->orderBuilder
            ->sizing_is_auto(false)
            ->setData([
                'sizing_volume' => 100,
                'sizing_weight' => 200,
            ])
            ->create();

        /** @var $inventory Inventory */
        $inventory = $this->inventoryBuilder
            ->order($order)
            ->setData([
                'is_section' => 0,
                'section_id' => 0,
                'price' => 100,
                'qty' => 2,
                'weight' => 10,
                'volume' => 5,
                'title' => 'Test Item',
                'sort' => 1,
            ])
            ->create();

        // Act
        $response = $this->deleteJson(route('orders.inventory.delete', [
            'orderId' => $order->id,
            'inventoryId' => $inventory->id
        ]));

        // Assert
        $response->assertStatus(200);

        // Refresh the order
        $order->refresh();

        // Check that sizing values were not recalculated
        $this->assertEquals(100, $order->sizing_volume);
        $this->assertEquals(200, $order->sizing_weight);
    }

    /** @test */
    public function it_returns_404_when_trying_to_delete_non_existent_inventory()
    {
        $division = $this->divisionBuilder->misc([
            "tz" => "America/Chicago"
        ])->create();
        $this->session(['division' => $division->toArray()]);

        // Arrange
        $user = $this->loginUser();
        /** @var $order Order */
        $order = $this->orderBuilder->create();

        // Act
        $response = $this->deleteJson(route('orders.inventory.delete', [
            'orderId' => $order->id,
            'inventoryId' => 99999 // Non-existent ID
        ]));

        // Assert
        $response->assertStatus(404);
    }
}
