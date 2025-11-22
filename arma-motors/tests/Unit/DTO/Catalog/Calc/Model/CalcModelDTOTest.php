<?php

namespace Tests\Unit\DTO\Catalog\Calc\Model;

use App\DTO\Catalog\Calc\Model\CalcModelDTO;
use App\DTO\Catalog\Calc\Model\CalModelSparesDTO;
use App\DTO\Catalog\Calc\Model\CalModelWorkDTO;
use App\Models\Catalogs\Car\Brand;
use Tests\TestCase;

class CalcModelDTOTest extends TestCase
{
    /** @test */
    public function check_fill_by_renault()
    {
        $brand = Brand::query()->where('name', 'renault')->first();
        $this->assertTrue($brand->isRenault());

        $data = [
            'brandId' => $brand->id,
            'modelId' => 1,
            'mileageId' => 1,
            'engineVolumeId' => 1,
            'driveUnitId' => 1,
            'transmissionId' => 1,
            'fuelId' => 1,
            'works' => [
                [
                    'id' => 1,
                    'minutes' => 10
                ],
                [
                    'id' => 2,
                    'minutes' => 30
                ]
            ],
            'spares' => [
                [
                    'id' => 1,
                    'qty' => 10
                ],
                [
                    'id' => 2,
                    'qty' => 3
                ]
            ],
        ];

        $dto = CalcModelDTO::byArgs($data, $brand);

        $this->assertEquals($dto->getBrandId(), $data['brandId']);
        $this->assertEquals($dto->getModelId(), $data['modelId']);
        $this->assertEquals($dto->getMileageId(), $data['mileageId']);
        $this->assertEquals($dto->getEngineVolumeId(), $data['engineVolumeId']);

        $this->assertNull($dto->getDriveUnitId());
        $this->assertNull($dto->getTransmissionId());
        $this->assertNull($dto->getFuelId());

        $this->assertIsArray($dto->getWorks());
        $this->assertNotEmpty($dto->getWorks());

        foreach ($dto->getWorks() as $key => $work){
            /** @var $work CalModelWorkDTO */
            $this->assertTrue($work instanceof CalModelWorkDTO);
            $this->assertEquals($work->getId(), $data['works'][$key]['id']);
            $this->assertEquals($work->getMinutes(), $data['works'][$key]['minutes']);
        }

        foreach ($dto->getSpares() as $key => $spares){
            /** @var $spares CalModelSparesDTO */
            $this->assertTrue($spares instanceof CalModelSparesDTO);
            $this->assertEquals($spares->getId(), $data['spares'][$key]['id']);
            $this->assertEquals($spares->getQty(), $data['spares'][$key]['qty']);
        }
    }

    /** @test */
    public function check_fill_by_volvo()
    {
        $brand = Brand::query()->where('name', 'volvo')->first();
        $this->assertTrue($brand->isVolvo());

        $data = [
            'brandId' => $brand->id,
            'modelId' => 1,
            'mileageId' => 1,
            'engineVolumeId' => 1,
            'driveUnitId' => 1,
            'transmissionId' => 1,
            'fuelId' => 1,
            'works' => [
                [
                    'id' => 1,
                    'minutes' => 10
                ],
                [
                    'id' => 2,
                    'minutes' => 30
                ]
            ],
            'spares' => [
                [
                    'id' => 1,
                    'qty' => 10
                ],
                [
                    'id' => 2,
                    'qty' => 3
                ]
            ],
        ];

        $dto = CalcModelDTO::byArgs($data, $brand);

        $this->assertEquals($dto->getBrandId(), $data['brandId']);
        $this->assertEquals($dto->getModelId(), $data['modelId']);
        $this->assertEquals($dto->getMileageId(), $data['mileageId']);
        $this->assertEquals($dto->getEngineVolumeId(), $data['engineVolumeId']);
        $this->assertEquals($dto->getFuelId(), $data['fuelId']);

        $this->assertNull($dto->getDriveUnitId());
        $this->assertNull($dto->getTransmissionId());

        $this->assertIsArray($dto->getWorks());
        $this->assertNotEmpty($dto->getWorks());

        foreach ($dto->getWorks() as $key => $work){
            /** @var $work CalModelWorkDTO */
            $this->assertTrue($work instanceof CalModelWorkDTO);
            $this->assertEquals($work->getId(), $data['works'][$key]['id']);
            $this->assertEquals($work->getMinutes(), $data['works'][$key]['minutes']);
        }

        foreach ($dto->getSpares() as $key => $spares){
            /** @var $spares CalModelSparesDTO */
            $this->assertTrue($spares instanceof CalModelSparesDTO);
            $this->assertEquals($spares->getId(), $data['spares'][$key]['id']);
            $this->assertEquals($spares->getQty(), $data['spares'][$key]['qty']);
        }
    }

    /** @test */
    public function check_fill_by_mitsubishi()
    {
        $brand = Brand::query()->where('name', 'mitsubishi')->first();
        $this->assertTrue($brand->isMitsubishi());

        $data = [
            'brandId' => $brand->id,
            'modelId' => 1,
            'mileageId' => 1,
            'engineVolumeId' => 1,
            'driveUnitId' => 1,
            'transmissionId' => 1,
            'fuelId' => 1,
            'works' => [
                [
                    'id' => 1,
                    'minutes' => 10
                ],
                [
                    'id' => 2,
                    'minutes' => 30
                ]
            ],
            'spares' => [
                [
                    'id' => 1,
                    'qty' => 10
                ],
                [
                    'id' => 2,
                    'qty' => 3
                ]
            ],
        ];

        $dto = CalcModelDTO::byArgs($data, $brand);

        $this->assertEquals($dto->getBrandId(), $data['brandId']);
        $this->assertEquals($dto->getModelId(), $data['modelId']);
        $this->assertEquals($dto->getMileageId(), $data['mileageId']);
        $this->assertEquals($dto->getEngineVolumeId(), $data['engineVolumeId']);
        $this->assertEquals($dto->getFuelId(), $data['fuelId']);
        $this->assertEquals($dto->getDriveUnitId(), $data['driveUnitId']);
        $this->assertEquals($dto->getTransmissionId(), $data['transmissionId']);

        $this->assertIsArray($dto->getWorks());
        $this->assertNotEmpty($dto->getWorks());

        foreach ($dto->getWorks() as $key => $work){
            /** @var $work CalModelWorkDTO */
            $this->assertTrue($work instanceof CalModelWorkDTO);
            $this->assertEquals($work->getId(), $data['works'][$key]['id']);
            $this->assertEquals($work->getMinutes(), $data['works'][$key]['minutes']);
        }

        foreach ($dto->getSpares() as $key => $spares){
            /** @var $spares CalModelSparesDTO */
            $this->assertTrue($spares instanceof CalModelSparesDTO);
            $this->assertEquals($spares->getId(), $data['spares'][$key]['id']);
            $this->assertEquals($spares->getQty(), $data['spares'][$key]['qty']);
        }
    }
}
