<?php

namespace Tests\Unit\DTO\Catalog\Car;

use App\DTO\Catalog\Car\BrandEditDTO;
use App\ValueObjects\Money;
use Tests\TestCase;

class BrandEditDTOTest extends TestCase
{
    /** @test */
    public function check_fill_by_args()
    {
        $data = [
            'isMain' => true,
            'color' => 1,
            'sort' => 1,
            'active' => true,
            'name' => 'some name',
            'hourlyPayment' => 100.00,
            'discountHourlyPayment' => 90.00,
            'workIds' => [1,3],
            'mileageIds' => [1,3],
        ];

        $dto = BrandEditDTO::byArgs($data);

        $this->assertEquals($dto->getSort(), $data['sort']);
        $this->assertEquals($dto->getActive(), $data['active']);
        $this->assertEquals($dto->getIsMain(), $data['isMain']);
        $this->assertEquals($dto->getColor(), $data['color']);
        $this->assertEquals($dto->getName(), $data['name']);
        $this->assertTrue($dto->getHourlyPayment() instanceof Money);
        $this->assertTrue($dto->getDiscountHourlyPayment() instanceof Money);
        $this->assertEquals($dto->getHourlyPayment()->getValue(), $data['hourlyPayment']);
        $this->assertEquals($dto->getDiscountHourlyPayment()->getValue(), $data['discountHourlyPayment']);
        $this->assertIsArray($dto->getWorkIds());
        $this->assertIsArray($dto->getMileageIds());
        $this->assertNotEmpty($dto->getMileageIds());
        $this->assertNotEmpty($dto->getWorkIds());
        $this->assertTrue($dto->changeColor());
        $this->assertTrue($dto->changeActive());
        $this->assertTrue($dto->changeSort());
        $this->assertTrue($dto->changeIsMain());
        $this->assertTrue($dto->changeName());
        $this->assertTrue($dto->changeHourlyPayment());
        $this->assertTrue($dto->changeDiscountHourlyPayment());
    }

    /** @test */
    public function check_empty_by_args()
    {
        $data = [];

        $dto = BrandEditDTO::byArgs($data);

        $this->assertNull($dto->getSort());
        $this->assertNull($dto->getActive());
        $this->assertNull($dto->getIsMain());
        $this->assertNull($dto->getColor());
        $this->assertNull($dto->getName());
        $this->assertNull($dto->getHourlyPayment());
        $this->assertNull($dto->getDiscountHourlyPayment());
        $this->assertFalse($dto->changeColor());
        $this->assertFalse($dto->changeActive());
        $this->assertFalse($dto->changeSort());
        $this->assertFalse($dto->changeIsMain());
        $this->assertFalse($dto->changeName());
        $this->assertFalse($dto->changeHourlyPayment());
        $this->assertFalse($dto->changeDiscountHourlyPayment());
        $this->assertIsArray($dto->getWorkIds());
        $this->assertIsArray($dto->getMileageIds());
        $this->assertEmpty($dto->getMileageIds());
        $this->assertEmpty($dto->getWorkIds());
    }

    /** @test */
    public function check_some_field_by_args()
    {
        $data = [
            'sort' => 1,
            'active' => true,
        ];

        $dto = BrandEditDTO::byArgs($data);

        $this->assertNotNull($dto->getSort());
        $this->assertNotNull($dto->getActive());
        $this->assertNull($dto->getIsMain());
        $this->assertNull($dto->getColor());
        $this->assertNull($dto->getName());
        $this->assertNull($dto->getHourlyPayment());
        $this->assertNull($dto->getDiscountHourlyPayment());
        $this->assertFalse($dto->changeColor());
        $this->assertTrue($dto->changeActive());
        $this->assertTrue($dto->changeSort());
        $this->assertFalse($dto->changeIsMain());
        $this->assertFalse($dto->changeName());
        $this->assertFalse($dto->changeDiscountHourlyPayment());
        $this->assertIsArray($dto->getWorkIds());
        $this->assertIsArray($dto->getMileageIds());
        $this->assertEmpty($dto->getMileageIds());
        $this->assertEmpty($dto->getWorkIds());
    }
}

