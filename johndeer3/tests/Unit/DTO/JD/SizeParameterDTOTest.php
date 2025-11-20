<?php

namespace Tests\Unit\DTO\JD;

use App\DTO\JD\SizeParameterDTO;
use PHPUnit\Framework\TestCase;

class SizeParameterDTOTest extends TestCase
{
    /** @test */
    public function success(): void
    {
        $data = self::data();
        $dto = SizeParameterDTO::byArgs($data);

        $this->assertEquals($dto->jdID, $data['id']);
        $this->assertEquals($dto->name, $data['name']);
        $this->assertTrue($dto->status);
    }

    /** @test */
    public function success_status_false(): void
    {
        $data = self::data();
        $data['status'] = 0;
        $dto = SizeParameterDTO::byArgs($data);

        $this->assertEquals($dto->jdID, $data['id']);
        $this->assertEquals($dto->name, $data['name']);
        $this->assertFalse($dto->status);
    }

    public static function data(): array
    {
        return [
            'id' => 1,
            'name' => 'size',
            'status' => 1,
        ];
    }
}
