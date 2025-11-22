<?php

namespace Tests\Unit\DTO;

use App\DTO\NameTranslationDTO;
use Tests\TestCase;

class NameTranslationDTOTest extends TestCase
{
    /** @test */
    public function check_fill_by_args()
    {
        $data = [
            'lang' => 'ru',
            'name' => 'test_ru'
        ];

        $dto = NameTranslationDTO::byArgs($data);

        $this->assertEquals($dto->getName(), $data['name']);
        $this->assertEquals($dto->getLang(), $data['lang']);
        $this->assertNull($dto->getText());
    }

    /** @test */
    public function check_fill_with_text()
    {
        $data = [
            'lang' => 'ru',
            'text' => 'some text',
            'name' => 'test_ru'
        ];

        $dto = NameTranslationDTO::byArgs($data);

        $this->assertEquals($dto->getName(), $data['name']);
        $this->assertEquals($dto->getLang(), $data['lang']);
        $this->assertEquals($dto->getText(), $data['text']);
    }


    public function fail_empty_name()
    {
        $data = [
            'lang' => 'ru',
            'name' => ''
        ];

//        $this->expectException(\InvalidArgumentException::class);

        NameTranslationDTO::byArgs($data);
    }
}
