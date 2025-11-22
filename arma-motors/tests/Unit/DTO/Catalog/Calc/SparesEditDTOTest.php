<?php

namespace Tests\Unit\DTO\Catalog\Calc;

use App\DTO\Catalog\Calc\SparesEditDTO;
use Tests\TestCase;

class SparesEditDTOTest extends TestCase
{
    /** @test */
    public function check_fill_by_args()
    {
        $data = [
            'groupId' => 1,
            'active' => true,
            'name' => 'name',
            'price' => 7.5,
            'priceDiscount' => 7.6,
        ];

        $dto = SparesEditDTO::byArgs($data);

        $this->assertEquals($dto->getGroupId(), $data['groupId']);
        $this->assertEquals($dto->getActive(), $data['active']);
        $this->assertEquals($dto->getName(), $data['name']);
        $this->assertEquals($dto->getPrice(), $data['price']);
        $this->assertEquals($dto->getPriceDiscount(), $data['priceDiscount']);

        $this->assertTrue($dto->changeGroupId());
        $this->assertTrue($dto->changeActive());
        $this->assertTrue($dto->changeName());
        $this->assertTrue($dto->changePrice());
        $this->assertTrue($dto->changePriceDiscount());
    }

    /** @test */
    public function check_some_fields_by_args()
    {
        $data = [
            'price' => 7.5,
            'priceDiscount' => 7.6,
        ];

        $dto = SparesEditDTO::byArgs($data);

        $this->assertNull($dto->getGroupId());
        $this->assertNull($dto->getActive());
        $this->assertNull($dto->getName());
        $this->assertNotNull($dto->getPrice());
        $this->assertNotNull($dto->getPriceDiscount());

        $this->assertFalse($dto->changeGroupId());
        $this->assertFalse($dto->changeActive());
        $this->assertFalse($dto->changeName());
        $this->assertTrue($dto->changePrice());
        $this->assertTrue($dto->changePriceDiscount());
    }

    /** @test */
    public function check_empty_by_args()
    {
        $data = [];

        $dto = SparesEditDTO::byArgs($data);

        $this->assertNull($dto->getGroupId());
        $this->assertNull($dto->getActive());
        $this->assertNull($dto->getName());
        $this->assertNull($dto->getPrice());
        $this->assertNull($dto->getPriceDiscount());

        $this->assertFalse($dto->changeGroupId());
        $this->assertFalse($dto->changeActive());
        $this->assertFalse($dto->changeName());
        $this->assertFalse($dto->changePrice());
        $this->assertFalse($dto->changePriceDiscount());
    }
}

