<?php

namespace Tests\Unit\Models\Items;

use App\Models\Item;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AutocompleteTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Set up the test environment.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function it_returns_items_for_single_character_query()
    {
        // Arrange
        /** @var Item $item1 */
        $item1 = Item::factory()->create([
            'title' => 'Sofa',
            'division_ids' => [1]
        ]);

        /** @var Item $item2 */
        $item2 = Item::factory()->create([
            'title' => 'Chair',
            'division_ids' => [1]
        ]);

        /** @var Item $item3 */
        $item3 = Item::factory()->create([
            'title' => 'Table',
            'division_ids' => [1]
        ]);

        // Act
        $result = (new Item())->autocomplete('S', 1);

        // Assert
        $this->assertCount(1, $result);
        $this->assertEquals($item1->id, $result->first()->id);
    }

    /** @test */
    public function it_returns_items_for_multi_character_query()
    {
        // Arrange
        /** @var Item $item1 */
        $item1 = Item::factory()->create([
            'title' => 'Sofa',
            'division_ids' => [1]
        ]);

        /** @var Item $item2 */
        $item2 = Item::factory()->create([
            'title' => 'Chair with soft cushion',
            'division_ids' => [1]
        ]);

        /** @var Item $item3 */
        $item3 = Item::factory()->create([
            'title' => 'Table',
            'division_ids' => [1]
        ]);

        // Act
        $result = (new Item())->autocomplete('soft', 1);

        // Assert
        $this->assertCount(1, $result);
        $this->assertEquals($item2->id, $result->first()->id);
    }

    /** @test */
    public function it_returns_items_for_multi_word_query()
    {
        // Arrange
        /** @var Item $item1 */
        $item1 = Item::factory()->create([
            'title' => 'Sofa large',
            'division_ids' => [1]
        ]);

        /** @var Item $item2 */
        $item2 = Item::factory()->create([
            'title' => 'Chair with soft cushion',
            'division_ids' => [1]
        ]);

        /** @var Item $item3 */
        $item3 = Item::factory()->create([
            'title' => 'Table small',
            'division_ids' => [1]
        ]);

        // Act
        $result = (new Item())->autocomplete('soft chair', 1);

        // Assert
        $this->assertCount(1, $result);
        $this->assertEquals($item2->id, $result->first()->id);
    }

    /** @test */
    public function it_filters_by_division_id()
    {
        // Arrange
        /** @var Item $item1 */
        $item1 = Item::factory()->create([
            'title' => 'Sofa',
            'division_ids' => [1]
        ]);

        /** @var Item $item2 */
        $item2 = Item::factory()->create([
            'title' => 'Sofa',
            'division_ids' => [2]
        ]);

        // Act
        $result = (new Item())->autocomplete('Sofa', 1);

        // Assert
        $this->assertCount(1, $result);
        $this->assertEquals($item1->id, $result->first()->id);
    }

    /** @test */
    public function it_orders_results_by_priority()
    {
        // Arrange
        /** @var Item $item1 */
        $item1 = Item::factory()->create([
            'title' => 'Sofa large',
            'division_ids' => [1]
        ]);

        /** @var Item $item2 */
        $item2 = Item::factory()->create([
            'title' => 'Large Sofa',
            'division_ids' => [1]
        ]);

        /** @var Item $item3 */
        $item3 = Item::factory()->create([
            'title' => 'Sofa',
            'division_ids' => [1]
        ]);

        // Act
        $result = (new Item())->autocomplete('Sofa', 1);

        // Assert
        $this->assertCount(3, $result);
        // Exact match at the beginning should be first
        $this->assertEquals($item3->id, $result->first()->id);
    }
}
