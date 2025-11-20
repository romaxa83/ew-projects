<?php

namespace Tests\Unit\DTO\Page;

use App\DTO\Page\PageDto;
use App\DTO\SimpleTranslationDto;
use PHPUnit\Framework\TestCase;
use Tests\Unit\DTO\SimpleTranslationDtoTest;

class PageDtoTest extends TestCase
{
    /** @test */
    public function success(): void
    {
        $trans_en = SimpleTranslationDtoTest::data();
        $trans_ua = SimpleTranslationDtoTest::data("ua");

        $data = [
            "type" => "some type",
            "active" => true,
            "translations" => [
                "ua" => $trans_ua,
                "en" => $trans_en
            ]
        ];

        $dto = PageDto::byArgs($data);

        $this->assertEquals($dto->type, $data['type']);
        $this->assertEquals($dto->active, $data['active']);

        $this->assertNotEmpty($dto->getTranslations());
        foreach ($dto->getTranslations() as $item){
            /** @var $item SimpleTranslationDto */
            $this->assertTrue($item instanceof SimpleTranslationDto);
            $this->assertEquals($data['translations'][$item->lang]['name'], $item->name);
            $this->assertEquals($data['translations'][$item->lang]['text'], $item->text);
        }
    }

    /** @test */
    public function success_without_translation(): void
    {
        $data = [
            "type" => "some type",
        ];

        $dto = PageDto::byArgs($data);

        $this->assertEquals($dto->type, $data['type']);
        $this->assertTrue($dto->active);
        $this->assertEmpty($dto->getTranslations());
    }
}
