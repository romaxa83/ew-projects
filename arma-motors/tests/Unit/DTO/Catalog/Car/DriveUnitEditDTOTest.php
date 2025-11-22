<?php

namespace Tests\Unit\DTO\Catalog\Car;

use App\DTO\Catalog\Car\DriveUnitEditDTO;
use Tests\TestCase;

class DriveUnitEditDTOTest extends TestCase
{
    /** @test */
    public function check_fill_by_args()
    {
        $data = [
            'sort' => 1,
            'active' => true,
            'name' => 'unit',
        ];

        $dto = DriveUnitEditDTO::byArgs($data);

        $this->assertEquals($dto->getSort(), $data['sort']);
        $this->assertEquals($dto->getActive(), $data['active']);
        $this->assertEquals($dto->getName(), $data['name']);

        $this->assertTrue($dto->changeActive());
        $this->assertTrue($dto->changeSort());
        $this->assertTrue($dto->changeName());
    }

    /** @test */
    public function check_empty_by_args()
    {
        $data = [];

        $dto = DriveUnitEditDTO::byArgs($data);

        $this->assertFalse($dto->changeActive());
        $this->assertFalse($dto->changeSort());
        $this->assertFalse($dto->changeName());
        $this->assertNull($dto->getSort());
        $this->assertNull($dto->getActive());
        $this->assertNull($dto->getName());
    }

    /** @test */
    public function check_some_field_by_args()
    {
        $data = [
            'sort' => 1,
            'active' => true,
        ];

        $dto = DriveUnitEditDTO::byArgs($data);

        $this->assertTrue($dto->changeActive());
        $this->assertTrue($dto->changeSort());
        $this->assertFalse($dto->changeName());
        $this->assertNotNull($dto->getSort());
        $this->assertNotNull($dto->getActive());
        $this->assertNull($dto->getName());
    }
}



