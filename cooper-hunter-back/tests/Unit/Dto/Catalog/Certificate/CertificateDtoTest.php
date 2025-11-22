<?php

namespace Tests\Unit\Dto\Catalog\Certificate;

use App\Dto\Catalog\Certificate\CertificateDto;
use App\Exceptions\AssertDataException;
use Tests\TestCase;

class CertificateDtoTest extends TestCase
{
    /** @test */
    public function success_fill_by_args()
    {
        $data = static::data();

        $dto = CertificateDto::byArgs($data);

        $this->assertEquals($dto->getNumber(), $data['number']);
        $this->assertEquals($dto->getTypeId(), $data['type_id']);
        $this->assertEquals($dto->getLink(), $data['link']);
    }

    /** @test */
    public function success_fill_by_args_without_link()
    {
        $data = static::data();
        unset($data['link']);

        $dto = CertificateDto::byArgs($data);

        $this->assertNull($dto->getLink());
    }

    /** @test */
    public function fail_without_type_id()
    {
        $data = static::data();
        unset($data['type_id']);

        $this->expectException(AssertDataException::class);
        $this->expectExceptionMessage(__('exceptions.assert_data.field must exist', ['field' => 'type_id']));

        CertificateDto::byArgs($data);
    }

    /** @test */
    public function fail_without_number()
    {
        $data = static::data();
        unset($data['number']);

        $this->expectException(AssertDataException::class);
        $this->expectExceptionMessage(__('exceptions.assert_data.field must exist', ['field' => 'number']));

        CertificateDto::byArgs($data);
    }

    public static function data()
    {
        return [
            'number' => "AX4567iTT",
            'link' => 'https://youtube.com/bla-bla',
            'type_id' => 2,
        ];
    }
}

