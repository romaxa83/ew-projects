<?php

namespace Tests\Unit\DTO\Order;

use App\DTO\Order\OrderEditDTO;
use Tests\TestCase;

class OrderEditDTOTest extends TestCase
{
    /** @test */
    public function check_fill_by_args()
    {
        $data = [
            'adminId' => 1,
            'status' => 2,
        ];

        $dto = OrderEditDTO::byArgs($data);
        $this->assertEquals($dto->getAdminId(), $data['adminId']);
        $this->assertEquals($dto->getStatus(), $data['status']);
        $this->assertTrue($dto->changeAdminId());
        $this->assertTrue($dto->changeStatus());
    }

    /** @test */
    public function check_empty()
    {
        $data = [];

        $dto = OrderEditDTO::byArgs($data);
        $this->assertNull($dto->getAdminId());
        $this->assertNull($dto->getStatus());
        $this->assertFalse($dto->changeAdminId());
        $this->assertFalse($dto->changeStatus());
    }

    /** @test */
    public function check_not_all()
    {
        $data = [
            'status' => 2,
        ];

        $dto = OrderEditDTO::byArgs($data);
        $this->assertEquals($dto->getStatus(), $data['status']);
        $this->assertNull($dto->getAdminId());
        $this->assertFalse($dto->changeAdminId());
        $this->assertTrue($dto->changeStatus());
    }
}
