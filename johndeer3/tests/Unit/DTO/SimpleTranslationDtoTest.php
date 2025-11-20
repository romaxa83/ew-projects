<?php

namespace Tests\Unit\DTO;

use App\DTO\SimpleTranslationDto;
use PHPUnit\Framework\TestCase;

class SimpleTranslationDtoTest extends TestCase
{
    /** @test */
    public function success(): void
    {
        $data = self::data();
        $dto = SimpleTranslationDto::byArgs($data);

        $this->assertEquals($dto->name, $data['name']);
        $this->assertEquals($dto->text, $data['text']);
        $this->assertEquals($dto->lang, $data['lang']);
    }

    /** @test */
    public function success_only_required(): void
    {
        $data = self::data();
        unset($data['text']);

        $dto = SimpleTranslationDto::byArgs($data);

        $this->assertEquals($dto->name, $data['name']);
        $this->assertEquals($dto->lang, $data['lang']);
        $this->assertNull($dto->text);
    }

    public static function data($lang = 'en'): array
    {
        return [
            'name' => "some title {$lang}",
            'text' => "some text {$lang}",
            'lang' => $lang,
        ];
    }
}


