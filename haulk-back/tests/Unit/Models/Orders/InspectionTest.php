<?php

namespace Tests\Unit\Models\Orders;

use App\Models\Orders\Inspection;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class InspectionTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_create_inspection_by_factory()
    {
        /** @var Inspection $inspection */
        $inspection = Inspection::factory()->create();

        $this->assertDatabaseHas(
            Inspection::TABLE_NAME,
            [
                'id' => $inspection->id
            ]
        );
    }
}
