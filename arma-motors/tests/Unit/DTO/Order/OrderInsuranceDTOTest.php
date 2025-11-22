<?php

namespace Tests\Unit\DTO\Order;

use App\DTO\Order\OrderInsuranceDTO;
use App\Types\Communication;
use App\ValueObjects\Money;
use Tests\TestCase;

class OrderInsuranceDTOTest extends TestCase
{
    /** @test */
    public function check_fill_by_args_for_casco()
    {
        $data = [
            'serviceId' => 1,
            'franchiseId' => 2,
            'communication' => Communication::PHONE,
            'brandId' => 3,
            'modelId' => 4,
            'driverAgeId' => 1,
            'insuranceCompany' => 'ams',
            'countPayments' => 3,
            'carCost' => 100,
        ];

        $dto = OrderInsuranceDTO::byArgs($data);
        $this->assertEquals($dto->getServiceId(), $data['serviceId']);
        $this->assertEquals($dto->getFranchiseId(), $data['franchiseId']);
        $this->assertEquals($dto->getCommunication(), $data['communication']);
        $this->assertEquals($dto->getBrandId(), $data['brandId']);
        $this->assertEquals($dto->getModelId(), $data['modelId']);
        $this->assertEquals($dto->getDriverAgeId(), $data['driverAgeId']);
        $this->assertEquals($dto->getInsuranceCompany(), $data['insuranceCompany']);
        $this->assertEquals($dto->getCountPayments(), $data['countPayments']);
        $this->assertTrue($dto->getCarCost() instanceof Money);
        $this->assertEquals($dto->getCarCost()->getValue(), $data['carCost']);
        $this->assertNull($dto->getRegionId());
        $this->assertNull($dto->getCityId());
        $this->assertNull($dto->getPrivilegesId());
        $this->assertNull($dto->getTransportTypeId());
        $this->assertNull($dto->getDurationId());
        $this->assertFalse($dto->getUseTaxi());
    }

    /** @test */
    public function check_fill_by_args_for_go()
    {
        $data = [
            'serviceId' => 1,
            'franchiseId' => 2,
            'communication' => Communication::PHONE,
            'regionId' => 3,
            'cityId' => 4,
            'privilegesId' => 5,
            'transportTypeId' => 6,
            'durationId' => 7,
            'useTaxi' => true,
        ];

        $dto = OrderInsuranceDTO::byArgs($data);
        $this->assertEquals($dto->getServiceId(), $data['serviceId']);
        $this->assertEquals($dto->getFranchiseId(), $data['franchiseId']);
        $this->assertEquals($dto->getCommunication(), $data['communication']);

        $this->assertEquals($dto->getRegionId(), $data['regionId']);
        $this->assertEquals($dto->getCityId(), $data['cityId']);
        $this->assertEquals($dto->getPrivilegesId(), $data['privilegesId']);
        $this->assertEquals($dto->getTransportTypeId(), $data['transportTypeId']);
        $this->assertEquals($dto->getDurationId(), $data['durationId']);
        $this->assertTrue($dto->getUseTaxi());
    }

    /** @test */
    public function check_fill_not_require_by_args()
    {
        $data = [
            'serviceId' => 1,
            'franchiseId' => 1,
            'communication' => Communication::PHONE,
        ];

        $dto = OrderInsuranceDTO::byArgs($data);

        $this->assertEquals($dto->getServiceId(), $data['serviceId']);
        $this->assertEquals($dto->getFranchiseId(), $data['franchiseId']);
        $this->assertEquals($dto->getCommunication(), $data['communication']);

        $this->assertNull($dto->getBrandId());
        $this->assertNull($dto->getModelId());
        $this->assertNull($dto->getDriverAgeId());
        $this->assertNull($dto->getDriverAgeId());
        $this->assertNull($dto->getInsuranceCompany());
        $this->assertNull($dto->getCountPayments());
        $this->assertNull($dto->getCarCost());
        $this->assertNull($dto->getRegionId());
        $this->assertNull($dto->getCityId());
        $this->assertNull($dto->getPrivilegesId());
        $this->assertNull($dto->getTransportTypeId());
        $this->assertNull($dto->getDurationId());
        $this->assertFalse($dto->getUseTaxi());
    }

    /** @test */
    public function check_empty()
    {
        $data = [];

        $this->expectException(\InvalidArgumentException::class);

        OrderInsuranceDTO::byArgs($data);
    }
}


