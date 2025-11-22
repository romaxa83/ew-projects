<?php

namespace Tests\Unit\DTO\Catalog\Calc;

use App\DTO\Catalog\Calc\MileageEditDTO;
use Tests\TestCase;

class MileageEditDTOTest extends TestCase
{
    /** @test */
    public function check_fill_by_args()
    {
        $data = [
            'value' => 1,
            'active' => true,
        ];

        $dto = MileageEditDTO::byArgs($data);

        $this->assertEquals($dto->getValue(), $data['value']);
        $this->assertEquals($dto->getActive(), $data['active']);

        $this->assertTrue($dto->changeActive());
        $this->assertTrue($dto->changeValue());
    }

    /** @test */
    public function check_empty_by_args()
    {
        $data = [];

        $dto = MileageEditDTO::byArgs($data);

        $this->assertFalse($dto->changeActive());
        $this->assertFalse($dto->changeValue());
        $this->assertNull($dto->getValue());
        $this->assertNull($dto->getActive());
    }

    /** @test */
    public function check_some_field_by_args()
    {
        $data = [
            'active' => true,
        ];

        $dto = MileageEditDTO::byArgs($data);

        $this->assertTrue($dto->changeActive());
        $this->assertFalse($dto->changeValue());
        $this->assertNull($dto->getValue());
        $this->assertNotNull($dto->getActive());
    }
}
