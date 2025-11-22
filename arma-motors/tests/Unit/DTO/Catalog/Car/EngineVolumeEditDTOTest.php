<?php

namespace Tests\Unit\DTO\Catalog\Car;

use App\DTO\Catalog\Car\EngineVolumeEditDTO;
use App\ValueObjects\Volume;
use Tests\TestCase;

class EngineVolumeEditDTOTest extends TestCase
{
    /** @test */
    public function check_fill_by_args()
    {
        $data = [
            'sort' => 1,
            'active' => true,
            'volume' => 2.4,
        ];

        $dto = EngineVolumeEditDTO::byArgs($data);

        $this->assertEquals($dto->getSort(), $data['sort']);
        $this->assertEquals($dto->getActive(), $data['active']);
        $this->assertTrue($dto->getVolume() instanceof Volume);
        $this->assertEquals($dto->getVolume()->getValue(), $data['volume']);

        $this->assertTrue($dto->changeActive());
        $this->assertTrue($dto->changeSort());
        $this->assertTrue($dto->changeVolume());
    }

    /** @test */
    public function check_empty_by_args()
    {
        $data = [];

        $dto = EngineVolumeEditDTO::byArgs($data);

        $this->assertFalse($dto->changeActive());
        $this->assertFalse($dto->changeSort());
        $this->assertFalse($dto->changeVolume());
        $this->assertNull($dto->getSort());
        $this->assertNull($dto->getActive());
        $this->assertNull($dto->getVolume());
    }

    /** @test */
    public function check_some_field_by_args()
    {
        $data = [
            'sort' => 1,
            'active' => true,
        ];

        $dto = EngineVolumeEditDTO::byArgs($data);

        $this->assertTrue($dto->changeActive());
        $this->assertTrue($dto->changeSort());
        $this->assertFalse($dto->changeVolume());
        $this->assertNotNull($dto->getSort());
        $this->assertNotNull($dto->getActive());
        $this->assertNull($dto->getVolume());
    }
}



