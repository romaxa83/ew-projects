<?php

namespace Tests\Unit\Models\Items\Group;

use App\Models\Item\Group;
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
    public function it_returns_groups_for_query()
    {
        // Arrange
        /** @var Group $group1 */
        $group1 = Group::factory()->create([
            'title' => 'Furniture'
        ]);

        /** @var Group $group2 */
        $group2 = Group::factory()->create([
            'title' => 'Electronics'
        ]);

        /** @var Group $group3 */
        $group3 = Group::factory()->create([
            'title' => 'Kitchen'
        ]);

        // Act
        $result = (new Group())->autocomplete('Fur');

        // Assert
        $this->assertCount(1, $result);
        $this->assertEquals($group1->id, $result->first()->id);
    }

    /** @test */
    public function it_returns_groups_for_multi_word_query()
    {
        // Arrange
        /** @var Group $group1 */
        $group1 = Group::factory()->create([
            'title' => 'Living Room Furniture'
        ]);

        /** @var Group $group2 */
        $group2 = Group::factory()->create([
            'title' => 'Kitchen Appliances'
        ]);

        /** @var Group $group3 */
        $group3 = Group::factory()->create([
            'title' => 'Bedroom Furniture'
        ]);

        // Act
        $result = (new Group())->autocomplete('living furniture');

        // Assert
        $this->assertCount(1, $result);
        $this->assertEquals($group1->id, $result->first()->id);
    }

    /** @test */
    public function it_orders_results_by_priority()
    {
        // Arrange
        /** @var Group $group1 */
        $group1 = Group::factory()->create([
            'title' => 'Furniture Large'
        ]);

        /** @var Group $group2 */
        $group2 = Group::factory()->create([
            'title' => 'Large Furniture'
        ]);

        /** @var Group $group3 */
        $group3 = Group::factory()->create([
            'title' => 'Furniture'
        ]);

        // Act
        $result = (new Group())->autocomplete('Furniture');

        // Assert
        $this->assertCount(3, $result);
        // Exact match at the beginning should be first
        $this->assertEquals($group3->id, $result->first()->id);
    }

    /** @test */
    public function it_limits_results_to_fifteen_items()
    {
        // Arrange
        // Create 20 groups with similar names
        $groups = [];
        for ($i = 1; $i <= 20; $i++) {
            /** @var Group $group */
            $groups[] = Group::factory()->create([
                'title' => "Test Group {$i}"
            ]);
        }

        // Act
        $result = (new Group())->autocomplete('Test');

        // Assert
        $this->assertCount(15, $result);
    }
}
