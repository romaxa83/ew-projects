<?php

namespace Tests\Unit\DTO\Catalog\Calc;

use App\DTO\Catalog\Calc\MileageDTO;
use Tests\TestCase;

class MileageDTOTest extends TestCase
{
    /** @test */
    public function check_fill_by_args()
    {
        $data = [
            'value' => 10000,
            'active' => false,
        ];

        $dto = MileageDTO::byArgs($data);

        $this->assertEquals($dto->getValue(), $data['value']);
        $this->assertEquals($dto->getActive(), $data['active']);
    }

    /** @test */
    public function check_required_fields()
    {
        $data = [
            'value' => 10000,
        ];

        $dto = MileageDTO::byArgs($data);

        $this->assertEquals($dto->getValue(), $data['value']);
        $this->assertTrue($dto->getActive());
    }

    /** @test */
    public function fail_empty()
    {
        $data = [];

        $this->expectException(\InvalidArgumentException::class);

        MileageDTO::byArgs($data);
    }
}
