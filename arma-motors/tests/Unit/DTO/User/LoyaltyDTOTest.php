<?php

namespace Tests\Unit\DTO\User;

use App\DTO\User\LoyaltyDTO;
use App\Models\User\Loyalty\Loyalty;
use Tests\TestCase;

class LoyaltyDTOTest extends TestCase
{
    /** @test */
    public function check_fill_by_args()
    {
        $data = [
            'type' => Loyalty::TYPE_SERVICE,
            'brandId' => 1,
            'age' => '2',
            'discount' => 2.1,
            'active' => false,
        ];

        $dto = LoyaltyDTO::byArgs($data);

        $this->assertEquals($dto->getType(), $data['type']);
        $this->assertEquals($dto->getBrandId(), $data['brandId']);
        $this->assertEquals($dto->getAge(), $data['age']);
        $this->assertEquals($dto->getDiscount(), $data['discount']);
        $this->assertEquals($dto->getActive(), $data['active']);
    }

    /** @test */
    public function check_required_fields()
    {
        $data = [
            'type' => Loyalty::TYPE_SERVICE,
            'brandId' => 1,
            'discount' => 2.1,
        ];

        $dto = LoyaltyDTO::byArgs($data);

        $this->assertEquals($dto->getType(), $data['type']);
        $this->assertEquals($dto->getBrandId(), $data['brandId']);
        $this->assertEquals($dto->getDiscount(), $data['discount']);
        $this->assertNull($dto->getAge());
        $this->assertTrue($dto->getActive());
    }

    /** @test */
    public function fail_empty()
    {
        $data = [];

        $this->expectException(\InvalidArgumentException::class);

        LoyaltyDTO::byArgs($data);
    }
}
