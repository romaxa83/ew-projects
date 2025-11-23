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

class AddTest extends TestCase
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
    public function it_can_add_inventory_to_order()
    {
        $division = $this->divisionBuilder->misc([
            "tz" => "America/Chicago"
        ])->create();
        $this->session(['division' => $division->toArray()]);

        // Arrange
        $user = $this->loginUser();
        /** @var $order Order  */
        $order = $this->orderBuilder->create();

        $data = [
            'is_section' => 0,
            'section_id' => null,
            'price' => 100,
            'qty' => 2,
            'weight' => 10,
            'volume' => 5,
            'title' => 'Test Item',
            'sort' => 1,
            'item_id' => null,
        ];

        // Act
        // Use direct URL instead of route name
        $response = $this->postJson(route('orders.inventory.add', ['id' => $order->id]), $data);

        // Assert
        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'record' => [
                    'id' => $order->id,
                ]
            ])
            ->assertJsonCount(1, 'record.inventories')
            ->assertJsonStructure([
                'meta' => [
                    'inventory'
                ]
            ])
            ->assertJson([
                'meta' => [
                    'inventory' => [
                        'order_id' => $order->id,
                        'is_section' => 0,
                        'title' => 'Test Item',
                        'price' => 100,
                        'qty' => 2,
                        'weight' => 10,
                        'volume' => 5,
                    ]
                ]
            ])
        ;

        $this->assertDatabaseHas(Inventory::TABLE, [
            'order_id' => $order->id,
            'is_section' => 0,
            'price' => 100,
            'qty' => 2,
            'weight' => 10,
            'volume' => 5,
            'title' => 'Test Item',
            'sort' => 1,
        ]);
    }

    /** @test */
    public function it_can_add_nested_inventory_to_order()
    {
        $division = $this->divisionBuilder->misc([
            "tz" => "America/Chicago"
        ])->create();
        $this->session(['division' => $division->toArray()]);

        // Arrange
        $user = $this->loginUser();
        /** @var $order Order  */
        $order = $this->orderBuilder->create();
        /** @var $inventory Order\Inventory  */
        $inventory = $this->inventoryBuilder
            ->order($order)
            ->is_section(1)
            ->section_id(null)
            ->create();

        $order->refresh();

        $data = [
            'is_section' => 0,
            'section_id' => $inventory->id,
            'price' => 100,
            'qty' => 2,
            'weight' => 10,
            'volume' => 5,
            'title' => 'Test Item',
            'sort' => 1,
            'item_id' => null,
        ];

        $this->assertCount(1, $order->inventories);
        $this->assertEmpty($order->inventories[0]->children);


        // Act
        // Use direct URL instead of route name
        $response = $this->postJson(route('orders.inventory.add', ['id' => $order->id]), $data);

        // Assert
        $response
            ->assertStatus(200)
            ->assertJson([
                'success' => true,
                'record' => [
                    'id' => $order->id,
                ]
            ])
            ->assertJsonCount(1, 'record.inventories')
            ->assertJsonCount(1, 'record.inventories.0.children')
        ;
    }

    /** @test */
    public function it_creates_inventory_activity_record_when_adding_inventory()
    {
        $division = $this->divisionBuilder->misc([
            "tz" => "America/Chicago"
        ])->create();
        $this->session(['division' => $division->toArray()]);

        // Arrange
        $user = $this->loginUser();

        /** @var $order Order  */
        $order = $this->orderBuilder->create();

        $data = [
            'is_section' => 0,
            'section_id' => null,
            'price' => 100,
            'qty' => 2,
            'weight' => 10,
            'volume' => 5,
            'title' => 'Test Item',
            'sort' => 1,
            'item_id' => null,
        ];

        // Act
        $response = $this->postJson(route('orders.inventory.add', $order->id), $data)
        ;

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'meta' => [
                    'inventory'
                ]
            ])
        ;

        // Get the newly created inventory
        $inventory = Order\Inventory::where('order_id', $order->id)->first();

        // Verify that the inventory in the response matches the one in the database
        $this->assertEquals($inventory->id, $response->json('meta.inventory.id'));

        // Check that an InventoryActivity record was created
        $this->assertDatabaseHas(InventoryActivity::TABLE, [
            'order_id' => $order->id,
            'client_id' => $order->client_id,
            'action' => 'create',
        ]);

        // Get the activity record
        $activity = InventoryActivity::where('order_id', $order->id)->first();

        // Check that the activity record has the correct miscs data
        $this->assertEquals($inventory->id, $activity->miscs['inventory_id']);
        $this->assertEquals('Test Item', $activity->miscs['title']);
        $this->assertEquals(2, $activity->miscs['qty']);
    }

    /** @test */
    public function it_creates_communication_record_when_adding_inventory()
    {
        $division = $this->divisionBuilder->misc([
            "tz" => "America/Chicago"
        ])->create();
        $this->session(['division' => $division->toArray()]);

        // Arrange
        $user = $this->loginUser();
        /** @var $order Order  */
        $order = $this->orderBuilder->create();

        $data = [
            'is_section' => 0,
            'section_id' => null,
            'price' => 100,
            'qty' => 2,
            'weight' => 10,
            'volume' => 5,
            'title' => 'Test Item',
            'sort' => 1,
            'item_id' => null,
        ];

        // Act
        $response = $this->postJson(route('orders.inventory.add', $order->id), $data);

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'meta' => [
                    'inventory'
                ]
            ]);

        // Verify that the inventory exists in the database and matches the one in the response
        $inventory = Order\Inventory::where('order_id', $order->id)->first();
        $this->assertEquals($inventory->id, $response->json('meta.inventory.id'));

        // Get the activity record
        $activity = InventoryActivity::where('order_id', $order->id)->first();

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
    public function it_recalculates_sizing_when_adding_inventory_to_order_with_auto_sizing()
    {
        $division = $this->divisionBuilder->misc([
            "tz" => "America/Chicago"
        ])->create();
        $this->session(['division' => $division->toArray()]);

        // Arrange
        $user = $this->loginUser();
        /** @var $order Order  */
        $order = $this->orderBuilder
            ->sizing_is_auto(true)
            ->create();

        $data = [
            'is_section' => 0,
            'section_id' => null,
            'price' => 100,
            'qty' => 2,
            'weight' => 10,
            'volume' => 5,
            'title' => 'Test Item',
            'sort' => 1,
            'item_id' => null,
        ];

        $this->assertNull($order->sizing_volume);
        $this->assertNull($order->sizing_weight);

        // Act
        $response = $this->postJson(route('orders.inventory.add', $order->id), $data);

        // Assert
        $response->assertStatus(200);

        // Verify that the inventory exists in the database and matches the one in the response
        $inventory = Order\Inventory::where('order_id', $order->id)->first();
        $this->assertEquals($inventory->id, $response->json('meta.inventory.id'));

        // Refresh the order from the database
        $order->refresh();

        // Check that the sizing was recalculated
        // Volume = 5 * 2 = 10, Weight = 10 * 2 = 20
        $this->assertEquals(10, $order->sizing_volume);
        $this->assertEquals(20, $order->sizing_weight);
    }

    /** @test */
    public function it_recalculates_sizing_when_adding_inventory_to_order_without_auto_sizing()
    {
        $division = $this->divisionBuilder->misc([
            "tz" => "America/Chicago"
        ])->create();
        $this->session(['division' => $division->toArray()]);

        // Arrange
        $user = $this->loginUser();
        /** @var $order Order  */
        $order = $this->orderBuilder
            ->sizing_is_auto(false)
            ->create();

        $data = [
            'is_section' => 0,
            'section_id' => null,
            'price' => 100,
            'qty' => 2,
            'weight' => 10,
            'volume' => 5,
            'title' => 'Test Item',
            'sort' => 1,
            'item_id' => null,
        ];

        $this->assertNull($order->sizing_volume);
        $this->assertNull($order->sizing_weight);

        // Act
        $response = $this->postJson(route('orders.inventory.add', $order->id), $data);

        // Assert
        $response->assertStatus(200);

        // Verify that the inventory exists in the database and matches the one in the response
        $inventory = Order\Inventory::where('order_id', $order->id)->first();
        $this->assertEquals($inventory->id, $response->json('meta.inventory.id'));

        // Refresh the order from the database
        $order->refresh();

        $this->assertNull($order->sizing_volume);
        $this->assertNull($order->sizing_weight);
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
        /** @var $order Order  */
        $order = $this->orderBuilder->create();

        $data = [
            // Missing required is_section
            'section_id' => null,
            'price' => 100,
            'qty' => 2,
            'weight' => 10,
            'volume' => 5,
            'title' => 'Test Item',
            // Missing required sort
            'item_id' => null,
        ];

        // Act
        $response = $this->postJson(route('orders.inventory.add', $order->id), $data);

        // Assert
        $response->assertStatus(200)
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
        /** @var $order Order  */
        $order = $this->orderBuilder->create();

        $data = [
            'is_section' => 'invalid', // Should be 0 or 1
            'section_id' => 'not-an-integer', // Should be an integer
            'price' => 'not-a-number', // Should be numeric
            'qty' => 'not-an-integer', // Should be an integer
            'weight' => 'not-a-number', // Should be numeric
            'volume' => 'not-a-number', // Should be numeric
            'title' => str_repeat('a', 100), // Should be max 95 characters
            'sort' => 'not-an-integer', // Should be an integer
            'item_id' => 'not-an-integer', // Should be an integer
        ];

        // Act
        $response = $this->postJson(route('orders.inventory.add', $order->id), $data);

        // Assert
        $response->assertStatus(200)
            ->assertJsonValidationErrors([
                'is_section', 'section_id', 'price', 'qty',
                'weight', 'volume', 'title', 'sort', 'item_id'
            ]);
    }
}
