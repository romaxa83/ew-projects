<?php

namespace Tests\Unit\DTO\JD;

use App\DTO\JD\ProductDTO;
use PHPUnit\Framework\TestCase;

class ProductDTOTest extends TestCase
{
    /** @test */
    public function success(): void
    {
        $data = self::data();
        $dto = ProductDTO::byArgs($data);

        $this->assertEquals($dto->jdID, $data['id']);
        $this->assertEquals($dto->sizeName, $data['size_name']);
        $this->assertEquals($dto->jdModelDescriptionID, $data['model_description_id']);
        $this->assertEquals($dto->jdEquipmentGroupID, $data['equipment_group_id']);
        $this->assertEquals($dto->jdManufactureID, $data['manufacture_id']);
        $this->assertEquals($dto->jdSizeParameterID, $data['size_parameter_id']);
        $this->assertEquals($dto->type, $data['type']);
        $this->assertTrue($dto->status);
    }

    /** @test */
    public function success_status_false(): void
    {
        $data = self::data();
        $data['status'] = 0;
        $dto = ProductDTO::byArgs($data);

        $this->assertFalse($dto->status);
    }

    public static function data(): array
    {
        return [
            'id' => 1,
            'size_name' => 'size',
            'model_description_id' => 2,
            'equipment_group_id' => 3,
            'manufacture_id' => 4,
            'size_parameter_id' => 5,
            'status' => 1,
            'type' => 1,
        ];
    }
}
