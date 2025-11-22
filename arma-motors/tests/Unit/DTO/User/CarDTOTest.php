<?php

namespace Tests\Unit\DTO\User;

use App\DTO\User\CarDTO;
use App\Models\User\Car;
use App\ValueObjects\CarNumber;
use App\ValueObjects\CarVin;
use Tests\TestCase;

class CarDTOTest extends TestCase
{
    /** @test */
    public function check_fill_by_args()
    {
        $data = [
            'brandId' => 1,
            'modelId' => 1,
            'number' => 'AA1111AA',
            'vin' => 'AA1111AA',
            'year' => '2012',
            'isPersonal' => true
        ];

        $dto = CarDTO::byArgs($data);

        $this->assertTrue($dto->getNumber() instanceof CarNumber);
        $this->assertTrue($dto->getVin() instanceof CarVin);
        $this->assertEquals($dto->getBrandId(), $data['brandId']);
        $this->assertEquals($dto->getModelId(), $data['modelId']);
        $this->assertEquals($dto->getNumber(), $data['number']);
        $this->assertEquals($dto->getVin(), $data['vin']);
        $this->assertEquals($dto->getYear(), $data['year']);
        $this->assertEquals($dto->getIsPersonal(), $data['isPersonal']);
        $this->assertEquals($dto->getStatus(), Car::DRAFT);

        $dto->setStatus(Car::MODERATE);

        $this->assertEquals($dto->getStatus(), Car::MODERATE);

        $this->assertFalse($dto->getIsAddToApp());
        $dto->setIsAddToApp();
        $this->assertTrue($dto->getIsAddToApp());

        $this->assertFalse($dto->getIsVerify());
        $dto->setIsVerify();
        $this->assertTrue($dto->getIsVerify());
    }
}

