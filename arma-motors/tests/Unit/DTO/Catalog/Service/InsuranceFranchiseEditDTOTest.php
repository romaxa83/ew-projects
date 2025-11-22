<?php

namespace Tests\Unit\DTO\Catalog\Service;

use App\DTO\Catalog\Service\InsuranceFranchiseEditDTO;
use Tests\TestCase;

class InsuranceFranchiseEditDTOTest extends TestCase
{
    /** @test */
    public function check_fill_by_args()
    {
        $data = [
            'sort' => 1,
            'active' => true,
            'name' => 'some name',
            'insuranceIds' => [1,3]
        ];

        $dto = InsuranceFranchiseEditDTO::byArgs($data);

        $this->assertEquals($dto->getSort(), $data['sort']);
        $this->assertEquals($dto->getActive(), $data['active']);
        $this->assertEquals($dto->getName(), $data['name']);
        $this->assertIsArray($dto->getInsuranceIds());

        $this->assertTrue($dto->changeActive());
        $this->assertTrue($dto->changeSort());
        $this->assertTrue($dto->changeName());
        $this->assertFalse($dto->emptyInsuranceIds());
    }

    /** @test */
    public function check_empty_by_args()
    {
        $data = [];

        $dto = InsuranceFranchiseEditDTO::byArgs($data);

        $this->assertNull($dto->getSort());
        $this->assertNull($dto->getActive());
        $this->assertNull($dto->getName());
        $this->assertIsArray($dto->getInsuranceIds());

        $this->assertFalse($dto->changeActive());
        $this->assertFalse($dto->changeSort());
        $this->assertFalse($dto->changeName());
        $this->assertTrue($dto->emptyInsuranceIds());
    }

    /** @test */
    public function check_required_field_by_args()
    {
        $data = [
            'name' => 'update',
        ];

        $dto = InsuranceFranchiseEditDTO::byArgs($data);

        $this->assertFalse($dto->changeActive());
        $this->assertFalse($dto->changeSort());
        $this->assertTrue($dto->changeName());
        $this->assertTrue($dto->emptyInsuranceIds());
    }
}


