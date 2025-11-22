<?php

namespace Tests\Unit\DTO\Order;

use App\DTO\Order\OrderServiceDTO;
use App\Types\Communication;
use Tests\TestCase;

class OrderServiceDTOTest extends TestCase
{
    /** @test */
    public function check_fill_by_args_for_service()
    {
        $data = $this->data();

        $dto = OrderServiceDTO::byArgs($data);

        $this->assertEquals($dto->getServiceId(), $data['serviceId']);
        $this->assertEquals($dto->getCarId(), $data['carId']);
        $this->assertEquals($dto->getDealershipId(), $data['dealershipId']);
        $this->assertEquals($dto->getCommunication(), $data['communication']);
        $this->assertEquals($dto->getComment(), $data['comment']);
        $this->assertEquals($dto->getMileage(), $data['mileage']);
        $this->assertEquals($dto->getDate(), $data['date']);
        $this->assertEquals($dto->getTime(), $data['time']);
        $this->assertEquals($dto->getRecommendationId(), $data['recommendationId']);
        $this->assertEquals($dto->getAgreementId(), $data['agreementId']);
        $this->assertEquals($dto->getPostUuid(), $data['postUuid']);
    }

    /** @test */
    public function check_fill_by_args_for_service_if_comment_null_as_string()
    {
        $data = $this->data();
        $data['comment'] = 'null';

        $dto = OrderServiceDTO::byArgs($data);

        $this->assertNotEquals($dto->getComment(), $data['comment']);
        $this->assertNull($dto->getComment());
    }

    /** @test */
    public function check_fill_by_args_for_service_without_comment()
    {
        $data = $this->data();
        unset($data['comment']);

        $dto = OrderServiceDTO::byArgs($data);
        $this->assertEquals($dto->getServiceId(), $data['serviceId']);
        $this->assertEquals($dto->getCarId(), $data['carId']);
        $this->assertEquals($dto->getDealershipId(), $data['dealershipId']);
        $this->assertEquals($dto->getCommunication(), $data['communication']);
        $this->assertEquals($dto->getMileage(), $data['mileage']);
        $this->assertEquals($dto->getDate(), $data['date']);
        $this->assertEquals($dto->getTime(), $data['time']);

        $this->assertNull($dto->getComment());
    }

    /** @test */
    public function check_fill_by_args_for_service_without_recommendation()
    {
        $data = $this->data();
        unset($data['recommendationId']);

        $dto = OrderServiceDTO::byArgs($data);
        $this->assertNull($dto->getRecommendationId());
    }

    /** @test */
    public function without_post_uuid()
    {
        $data = $this->data();
        unset($data['postUuid']);

        $dto = OrderServiceDTO::byArgs($data);
        $this->assertNull($dto->getPostUuid());
    }

    /** @test */
    public function check_fill_by_args_for_service_without_agreement()
    {
        $data = $this->data();
        unset($data['agreementId']);

        $dto = OrderServiceDTO::byArgs($data);
        $this->assertNull($dto->getAgreementId());
    }

    /** @test */
    public function check_empty()
    {
        $data = [];

        $this->expectException(\InvalidArgumentException::class);

        OrderServiceDTO::byArgs($data);
    }

    public function data(): array
    {
        return [
            'serviceId' => 1,
            'recommendationId' => 1,
            'agreementId' => 1,
            'carId' => 2,
            'dealershipId' => 2,
            'communication' => Communication::PHONE,
            'comment' => 'some comment',
            'mileage' => 33333,
            'date' => 400,
            'time' => 100,
            'postUuid' => "safrwetert34532345",
        ];
    }
}


