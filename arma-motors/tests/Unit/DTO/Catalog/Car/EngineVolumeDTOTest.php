<?php

namespace Tests\Unit\DTO\Catalog\Car;

use App\DTO\Catalog\Car\EngineVolumeDTO;
use App\ValueObjects\Volume;
use Tests\TestCase;

class EngineVolumeDTOTest extends TestCase
{
    /** @test */
    public function check_fill_by_args()
    {
        $data = [
            'sort' => 1,
            'active' => false,
            'volume' => 20
        ];

        $dto = EngineVolumeDTO::byArgs($data);

        $this->assertEquals($dto->getSort(), $data['sort']);
        $this->assertEquals($dto->getActive(), $data['active']);
        $this->assertTrue($dto->getVolume() instanceof Volume);
        $this->assertEquals($dto->getVolume()->getValue(), $data['volume']);
    }

    /** @test */
    public function check_required_fields()
    {
        $data = [
            'volume' => 20
        ];

        $dto = EngineVolumeDTO::byArgs($data);

        $this->assertEquals($dto->getSort(), 0);
        $this->assertTrue($dto->getActive());
        $this->assertEquals($dto->getVolume()->getValue(), $data['volume']);
    }

    /** @test */
    public function fail_empty()
    {
        $data = [];

        $this->expectException(\InvalidArgumentException::class);

        EngineVolumeDTO::byArgs($data);
    }
}
