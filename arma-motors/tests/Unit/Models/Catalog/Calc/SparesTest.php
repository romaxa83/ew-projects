<?php

namespace Tests\Unit\Models\Catalog\Calc;

use App\Models\Catalogs\Calc\Spares;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SparesTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function get_list_type()
    {
        $types = Spares::listType();

        $this->assertIsArray($types);
        $this->assertCount(3, $types);
    }

    /** @test */
    public function check_type_true()
    {
        $this->assertTrue(Spares::checkType(Spares::TYPE_VOLVO));
    }

    /** @test */
    public function check_type_false()
    {
        $this->assertFalse(Spares::checkType('wrong'));
    }

    /** @test */
    public function asset_type()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(__('error.not valid car type for spares', ['type' => 'wrong']));

        Spares::assetType('wrong');
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function asset_type_success()
    {
        Spares::assetType(Spares::TYPE_VOLVO);
    }
}
