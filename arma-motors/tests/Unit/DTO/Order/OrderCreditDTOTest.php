<?php

namespace Tests\Unit\DTO\Order;

use App\DTO\Order\OrderCreditDTO;
use App\DTO\Order\OrderInsuranceDTO;
use App\Types\Communication;
use App\Types\UserType;
use App\ValueObjects\Money;
use Tests\TestCase;

class OrderCreditDTOTest extends TestCase
{
    /** @test */
    public function check_fill_by_args_for_casco()
    {
        $data = [
            'serviceId' => 1,
            'durationId' => 2,
            'communication' => Communication::PHONE,
            'brandId' => 3,
            'modelId' => 4,
            'firstInstallment' => 1,
            'typeUser' => UserType::TYPE_PERSONAL,
            'carCost' => 100,
        ];

        $dto = OrderCreditDTO::byArgs($data);
        $this->assertEquals($dto->getServiceId(), $data['serviceId']);
        $this->assertEquals($dto->getCommunication(), $data['communication']);
        $this->assertEquals($dto->getBrandId(), $data['brandId']);
        $this->assertEquals($dto->getModelId(), $data['modelId']);
        $this->assertTrue($dto->getCarCost() instanceof Money);
        $this->assertEquals($dto->getCarCost()->getValue(), $data['carCost']);
        $this->assertEquals($dto->getDurationId(), $data['durationId']);
        $this->assertEquals($dto->getTypeUser(), $data['typeUser']);
        $this->assertEquals($dto->getFirstInstallment(), $data['firstInstallment']);
    }

    /** @test */
    public function wrong_communication()
    {
        $data = [
            'serviceId' => 1,
            'durationId' => 2,
            'communication' => 'wrong',
            'brandId' => 3,
            'modelId' => 4,
            'firstInstallment' => 1,
            'typeUser' => UserType::TYPE_PERSONAL,
            'carCost' => 100,
        ];

        $this->expectException(\InvalidArgumentException::class);

        OrderCreditDTO::byArgs($data);
    }

    /** @test */
    public function wrong_type_user()
    {
        $data = [
            'serviceId' => 1,
            'durationId' => 2,
            'communication' => Communication::TELEGRAM,
            'brandId' => 3,
            'modelId' => 4,
            'firstInstallment' => 1,
            'typeUser' => 7,
            'carCost' => 100,
        ];

        $this->expectException(\InvalidArgumentException::class);

        OrderCreditDTO::byArgs($data);
    }

    /** @test */
    public function check_empty()
    {
        $data = [];

        $this->expectException(\InvalidArgumentException::class);

        OrderInsuranceDTO::byArgs($data);
    }
}


