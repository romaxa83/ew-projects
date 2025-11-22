<?php

namespace Tests\Unit\DTO\User;

use App\DTO\User\LoyaltyEditDTO;
use Tests\TestCase;

class LoyaltyEditDTOTest extends TestCase
{
    /** @test */
    public function check_fill_by_args()
    {
        $data = [
            'active' => true,
            'discount' => 10.0,
        ];

        $dto = LoyaltyEditDTO::byArgs($data);

        $this->assertEquals($dto->getDiscount(), $data['discount']);
        $this->assertEquals($dto->getActive(), $data['active']);

        $this->assertTrue($dto->changeActive());
        $this->assertTrue($dto->changeDiscount());
    }

    /** @test */
    public function check_empty_by_args()
    {
        $data = [];

        $dto = LoyaltyEditDTO::byArgs($data);

        $this->assertFalse($dto->changeActive());
        $this->assertFalse($dto->changeDiscount());
        $this->assertNull($dto->getActive());
        $this->assertNull($dto->getDiscount());
    }

    /** @test */
    public function check_some_field_by_args()
    {
        $data = [
            'discount' => 1.0,
        ];

        $dto = LoyaltyEditDTO::byArgs($data);

        $this->assertFalse($dto->changeActive());
        $this->assertTrue($dto->changeDiscount());
        $this->assertNull($dto->getActive());
        $this->assertNotNull($dto->getDiscount());
    }
}

