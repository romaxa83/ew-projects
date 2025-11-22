<?php

namespace Tests\Unit\DTO\Catalog\Car;

use App\DTO\Catalog\Car\DriveUnitDTO;
use Tests\TestCase;

class DriveUnitDTOTest extends TestCase
{
    /** @test */
    public function check_fill_by_args()
    {
        $data = [
            'sort' => 1,
            'active' => false,
            'name' => '2RD',
        ];

        $dto = DriveUnitDTO::byArgs($data);

        $this->assertEquals($dto->getSort(), $data['sort']);
        $this->assertEquals($dto->getActive(), $data['active']);
        $this->assertEquals($dto->getName(), $data['name']);
    }

    /** @test */
    public function check_required_fields()
    {
        $data = [
            'name' => '2RD',
        ];

        $dto = DriveUnitDTO::byArgs($data);

        $this->assertEquals($dto->getSort(), 0);
        $this->assertTrue($dto->getActive());
        $this->assertEquals($dto->getName(), $data['name']);
    }

    /** @test */
    public function fail_empty()
    {
        $data = [];

        $this->expectException(\InvalidArgumentException::class);

        DriveUnitDTO::byArgs($data);
    }
}

