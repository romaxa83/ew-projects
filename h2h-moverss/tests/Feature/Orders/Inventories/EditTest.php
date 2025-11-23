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

class EditTest extends TestCase
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
    public function it_can_edit_inventory_in_order()
    {
        $division = $this->divisionBuilder->misc([
            "tz" => "America/Chicago"
        ])->create();
        $this->session(['division' => $division->toArray()]);

        // Arrange
        $user = $this->loginUser();

        /** @var Order $order */
        $order = $this->orderBuilder->create();

        /** @var Inventory $inventory */
        $inventory = $this->inventoryBuilder
            ->order($order)
            ->setData([
                'is_section' => 0,
                'section_id' => 0,
                'price' => 100,
                'qty' => 2,
                'weight' => 10,
                'volume' => 5,
                'title' => 'Original Item',
                'sort' => 1,
                'item_id' => null,
            ])
            ->create();

        $data = [
            'is_section' => 0,
            'section_id' => 0,
            'price' => 150,
            'qty' => 3,
            'weight' => 15,
            'volume' => 7,
            'title' => 'Updated Item',
            'sort' => 1,
            'item_id' => null,
        ];

        // Act
        $response = $this->postJson(route('orders.inventory.edit', [
            'orderId' => $order->id,
            'inventoryId' => $inventory->id
        ]), $data);

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'record' => [
                    'id' => $order->id,
                ]
            ]);

        // Check that the inventory was updated
        $this->assertDatabaseHas(Inventory::TABLE, [
            'id' => $inventory->id,
            'order_id' => $order->id,
            'title' => 'Updated Item',
            'price' => 150,
            'qty' => 3,
            'weight' => 15,
            'volume' => 7,
        ]);
    }

    /** @test */
    public function it_creates_inventory_activity_record_when_editing_inventory()
    {
        $division = $this->divisionBuilder->misc([
            "tz" => "America/Chicago"
        ])->create();
        $this->session(['division' => $division->toArray()]);

        // Arrange
        $user = $this->loginUser();

        /** @var Order $order */
        $order = $this->orderBuilder->create();

        /** @var Inventory $inventory */
        $inventory = $this->inventoryBuilder
            ->order($order)
            ->setData([
                'is_section' => 0,
                'section_id' => 0,
                'price' => 100,
                'qty' => 2,
                'weight' => 10,
                'volume' => 5,
                'title' => 'Original Item',
                'sort' => 1,
                'item_id' => null,
            ])
            ->create();

        $data = [
            'is_section' => 0,
            'section_id' => 0,
            'price' => 150,
            'qty' => 3,
            'weight' => 15,
            'volume' => 7,
            'title' => 'Updated Item',
            'sort' => 1,
            'item_id' => null,
        ];

        // Act
        $response = $this->postJson(route('orders.inventory.edit', [
            'orderId' => $order->id,
            'inventoryId' => $inventory->id
        ]), $data);

        // Assert
        $response->assertStatus(200);

        // Check that an InventoryActivity record was created
        $this->assertDatabaseHas(InventoryActivity::TABLE, [
            'order_id' => $order->id,
            'client_id' => $order->client_id,
            'action' => 'update',
        ]);

        // Get the activity record
        $activity = InventoryActivity::where('order_id', $order->id)
            ->where('action', 'update')
            ->first();

        // Check that the activity record has the correct miscs data
        $this->assertEquals($inventory->id, $activity->miscs['inventory_id']);
        $this->assertEquals('Updated Item', $activity->miscs['title']);
        $this->assertEquals(3, $activity->miscs['qty']);
    }

    /** @test */
    public function it_creates_communication_record_when_editing_inventory()
    {
        $division = $this->divisionBuilder->misc([
            "tz" => "America/Chicago"
        ])->create();
        $this->session(['division' => $division->toArray()]);

        // Arrange
        $user = $this->loginUser();

        /** @var Order $order */
        $order = $this->orderBuilder->create();

        /** @var Inventory $inventory */
        $inventory = $this->inventoryBuilder
            ->order($order)
            ->setData([
                'is_section' => 0,
                'section_id' => 0,
                'price' => 100,
                'qty' => 2,
                'weight' => 10,
                'volume' => 5,
                'title' => 'Original Item',
                'sort' => 1,
                'item_id' => null,
            ])
            ->create();

        $data = [
            'is_section' => 0,
            'section_id' => 0,
            'price' => 150,
            'qty' => 3,
            'weight' => 15,
            'volume' => 7,
            'title' => 'Updated Item',
            'sort' => 1,
            'item_id' => null,
        ];

        // Act
        $response = $this->postJson(route('orders.inventory.edit', [
            'orderId' => $order->id,
            'inventoryId' => $inventory->id
        ]), $data);

        // Assert
        $response->assertStatus(200);

        // Get the activity record
        $activity = InventoryActivity::where('order_id', $order->id)
            ->where('action', 'update')
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
    public function it_recalculates_sizing_when_editing_inventory_in_order_with_auto_sizing()
    {
        $division = $this->divisionBuilder->misc([
            "tz" => "America/Chicago"
        ])->create();
        $this->session(['division' => $division->toArray()]);

        // Arrange
        $user = $this->loginUser();

        /** @var Order $order */
        $order = $this->orderBuilder
            ->sizing_is_auto(true)
            ->create();

        /** @var Inventory $inventory */
        $inventory = $this->inventoryBuilder
            ->order($order)
            ->setData([
                'is_section' => 0,
                'section_id' => 0,
                'price' => 100,
                'qty' => 2,
                'weight' => 10,
                'volume' => 5,
                'title' => 'Original Item',
                'sort' => 1,
                'item_id' => null,
            ])
            ->create();

        // Initial sizing values
        $order->recountSizingAuto();
        $initialVolume = $order->sizing_volume;
        $initialWeight = $order->sizing_weight;

        $data = [
            'is_section' => 0,
            'section_id' => 0,
            'price' => 150,
            'qty' => 3, // Changed from 2 to 3
            'weight' => 15, // Changed from 10 to 15
            'volume' => 7, // Changed from 5 to 7
            'title' => 'Updated Item',
            'sort' => 1,
            'item_id' => null,
        ];

        // Act
        $response = $this->postJson(route('orders.inventory.edit', [
            'orderId' => $order->id,
            'inventoryId' => $inventory->id
        ]), $data);

        // Assert
        $response->assertStatus(200);

        // Refresh the order
        $order->refresh();

        // Calculate expected values:
        // Original: volume = 5 * 2 = 10, weight = 10 * 2 = 20
        // Updated: volume = 7 * 3 = 21, weight = 15 * 3 = 45

        // Check that sizing values were recalculated
        $this->assertEquals(21, $order->sizing_volume);
        $this->assertEquals(45, $order->sizing_weight);
    }

    /** @test */
    public function it_does_not_recalculate_sizing_when_editing_inventory_in_order_without_auto_sizing()
    {
        $division = $this->divisionBuilder->misc([
            "tz" => "America/Chicago"
        ])->create();
        $this->session(['division' => $division->toArray()]);

        // Arrange
        $user = $this->loginUser();

        /** @var Order $order */
        $order = $this->orderBuilder
            ->sizing_is_auto(false)
            ->setData([
                'sizing_volume' => 100,
                'sizing_weight' => 200,
            ])
            ->create();

        /** @var Inventory $inventory */
        $inventory = $this->inventoryBuilder
            ->order($order)
            ->setData([
                'is_section' => 0,
                'section_id' => 0,
                'price' => 100,
                'qty' => 2,
                'weight' => 10,
                'volume' => 5,
                'title' => 'Original Item',
                'sort' => 1,
                'item_id' => null,
            ])
            ->create();

        $data = [
            'is_section' => 0,
            'section_id' => 0,
            'price' => 150,
            'qty' => 3,
            'weight' => 15,
            'volume' => 7,
            'title' => 'Updated Item',
            'sort' => 1,
            'item_id' => null,
        ];

        // Act
        $response = $this->postJson(route('orders.inventory.edit', [
            'orderId' => $order->id,
            'inventoryId' => $inventory->id
        ]), $data);

        // Assert
        $response->assertStatus(200);

        // Refresh the order
        $order->refresh();

        // Check that sizing values were not recalculated
        $this->assertEquals(100, $order->sizing_volume);
        $this->assertEquals(200, $order->sizing_weight);
    }

    /** @test */
    public function it_validates_required_fields()
    {
        $division = $this->divisionBuilder->misc([
            "tz" => "America/Chicago"
        ])->create();
        $this->session(['division' => $division->toArray()]);

        // Arrange
        $user = $this->loginUser();

        /** @var Order $order */
        $order = $this->orderBuilder->create();

        /** @var Inventory $inventory */
        $inventory = $this->inventoryBuilder
            ->order($order)
            ->setData([
                'is_section' => 0,
                'section_id' => 0,
                'title' => 'Original Item',
                'sort' => 1,
            ])
            ->create();

        // Act - missing required fields
        $response = $this->postJson(route('orders.inventory.edit', [
            'orderId' => $order->id,
            'inventoryId' => $inventory->id
        ]), []);

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'success' => false,
            ])
            ->assertJsonValidationErrors(['is_section', 'sort']);
    }

    /** @test */
    public function it_validates_field_types()
    {
        $division = $this->divisionBuilder->misc([
            "tz" => "America/Chicago"
        ])->create();
        $this->session(['division' => $division->toArray()]);

        // Arrange
        $user = $this->loginUser();

        /** @var Order $order */
        $order = $this->orderBuilder->create();

        /** @var Inventory $inventory */
        $inventory = $this->inventoryBuilder
            ->order($order)
            ->setData([
                'is_section' => 0,
                'section_id' => 0,
                'title' => 'Original Item',
                'sort' => 1,
            ])
            ->create();

        // Act - invalid field types
        $response = $this->postJson(route('orders.inventory.edit', [
            'orderId' => $order->id,
            'inventoryId' => $inventory->id
        ]), [
            'is_section' => 'not-a-number',
            'sort' => 'not-a-number',
            'price' => 'not-a-number',
            'qty' => 'not-a-number',
            'weight' => 'not-a-number',
            'volume' => 'not-a-number',
        ]);

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'success' => false,
            ])
            ->assertJsonValidationErrors(['is_section', 'sort', 'price', 'qty', 'weight', 'volume']);
    }
}
