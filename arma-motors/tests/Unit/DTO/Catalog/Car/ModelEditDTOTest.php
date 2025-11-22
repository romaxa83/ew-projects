<?php

namespace Tests\Unit\DTO\Catalog\Car;

use App\DTO\Catalog\Car\ModelEditDTO;
use Tests\TestCase;

class ModelEditDTOTest extends TestCase
{
    /** @test */
    public function check_fill_by_args()
    {
        $data = [
            'brandId' => 1,
            'name' => 'name',
            'sort' => 1,
            'active' => true,
            'forCredit' => true,
            'forCalc' => true,
        ];

        $dto = ModelEditDTO::byArgs($data);

        $this->assertEquals($dto->getSort(), $data['sort']);
        $this->assertEquals($dto->getActive(), $data['active']);
        $this->assertEquals($dto->getBrandId(), $data['brandId']);
        $this->assertEquals($dto->getName(), $data['name']);
        $this->assertEquals($dto->getForCalc(), $data['forCalc']);
        $this->assertEquals($dto->getForCredit(), $data['forCredit']);
        $this->assertTrue($dto->changeName());
        $this->assertTrue($dto->changeActive());
        $this->assertTrue($dto->changeSort());
        $this->assertTrue($dto->changeBrandId());
        $this->assertTrue($dto->changeForCredit());
        $this->assertTrue($dto->changeForCalc());
    }

    /** @test */
    public function check_empty_by_args()
    {
        $data = [];

        $dto = ModelEditDTO::byArgs($data);

        $this->assertNull($dto->getSort());
        $this->assertNull($dto->getActive());
        $this->assertNull($dto->getForCredit());
        $this->assertNull($dto->getForCalc());
        $this->assertNull($dto->getBrandId());
        $this->assertNull($dto->getName());
        $this->assertFalse($dto->changeBrandId());
        $this->assertFalse($dto->changeActive());
        $this->assertFalse($dto->changeSort());
        $this->assertFalse($dto->changeName());
        $this->assertFalse($dto->changeForCalc());
        $this->assertFalse($dto->changeForCredit());
    }

    /** @test */
    public function check_some_field_by_args()
    {
        $data = [
            'sort' => 1,
            'active' => true,
        ];

        $dto = ModelEditDTO::byArgs($data);

        $this->assertNotNull($dto->getSort());
        $this->assertNotNull($dto->getActive());
        $this->assertNull($dto->getBrandId());
        $this->assertNull($dto->getName());
        $this->assertNull($dto->getForCalc());
        $this->assertNull($dto->getForCredit());
        $this->assertFalse($dto->changeBrandId());
        $this->assertTrue($dto->changeActive());
        $this->assertTrue($dto->changeSort());
        $this->assertFalse($dto->changeName());
        $this->assertFalse($dto->changeForCredit());
        $this->assertFalse($dto->changeForCalc());
    }
}
