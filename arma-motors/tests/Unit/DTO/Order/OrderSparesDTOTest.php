<?php

namespace Tests\Unit\DTO\Order;

use App\DTO\Order\OrderSparesDTO;
use App\Types\Communication;
use Tests\TestCase;

class OrderSparesDTOTest extends TestCase
{
    /** @test */
    public function check_fill_by_args_for_service()
    {
        $data = $this->data();

        $dto = OrderSparesDTO::byArgs($data);
        $this->assertEquals($dto->getServiceId(), $data['serviceId']);
        $this->assertEquals($dto->getCarId(), $data['carId']);
        $this->assertEquals($dto->getCommunication(), $data['communication']);
        $this->assertEquals($dto->getComment(), $data['comment']);
        $this->assertEquals($dto->getRecommendationId(), $data['recommendationId']);
    }

    /** @test */
    public function check_fill_by_args_for_service_without_recommendation()
    {
        $data = $this->data();
        unset($data['recommendationId']);

        $dto = OrderSparesDTO::byArgs($data);

        $this->assertNull($dto->getRecommendationId());
    }

    /** @test */
    public function check_empty()
    {
        $data = [];

        $this->expectException(\InvalidArgumentException::class);

        OrderSparesDTO::byArgs($data);
    }

    public function data(): array
    {
        return [
            'serviceId' => 1,
            'recommendationId' => 1,
            'carId' => 2,
            'communication' => Communication::PHONE,
            'comment' => 'some comment',
        ];
    }
}
