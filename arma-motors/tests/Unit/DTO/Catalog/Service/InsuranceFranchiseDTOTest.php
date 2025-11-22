<?php

namespace Tests\Unit\DTO\Catalog\Service;

use App\DTO\Catalog\Service\InsuranceFranchiseDTO;
use Tests\TestCase;

class InsuranceFranchiseDTOTest extends TestCase
{
    /** @test */
    public function check_fill_by_args()
    {
        $data = [
            'sort' => 1,
            'active' => false,
            'name' => 'some name',
            'insuranceIds' => [1, 2]
        ];

        $dto = InsuranceFranchiseDTO::byArgs($data);

        $this->assertEquals($dto->getSort(), $data['sort']);
        $this->assertEquals($dto->getActive(), $data['active']);
        $this->assertEquals($dto->getName(), $data['name']);
        $this->assertNotEmpty($dto->getInsuranceIds());
        $this->assertFalse($dto->emptyInsuranceIds());
    }

    /** @test */
    public function check_only_name()
    {
        $data = [
            'name' => 'some name'
        ];

        $dto = InsuranceFranchiseDTO::byArgs($data);

        $this->assertEquals($dto->getSort(), 0);
        $this->assertTrue($dto->getActive());
        $this->assertEquals($dto->getName(), $data['name']);
        $this->assertEmpty($dto->getInsuranceIds());
        $this->assertTrue($dto->emptyInsuranceIds());
    }

    /** @test */
    public function empty_data()
    {
        $data = [];

        $this->expectException(\InvalidArgumentException::class);

        InsuranceFranchiseDTO::byArgs($data);
    }
}

