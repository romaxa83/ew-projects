<?php

namespace Tests\Unit\DTO\Locale;

use App\DTO\Locale\TranslationDTO;
use App\DTO\Locale\TranslationsDTO;
use App\Models\Translate;
use PHPUnit\Framework\TestCase;

class TranslationsDTOTest extends TestCase
{
    /** @test */
    public function success(): void
    {
        $data = [
            "button" => [
                "en" => "button en"
            ]
        ];
        $dto = TranslationsDTO::byRequestFromApp($data);

        $this->assertCount(1, $dto->getDtos());
        $this->assertTrue($dto->getDtos()[0] instanceof TranslationDTO);
        $this->assertEquals($dto->getDtos()[0]->model, Translate::TYPE_SITE);
        $this->assertEquals($dto->getDtos()[0]->lang, "en");
        $this->assertEquals($dto->getDtos()[0]->alias, "button");
        $this->assertEquals($dto->getDtos()[0]->text, $data["button"]["en"]);
        $this->assertNull($dto->getDtos()[0]->entity_type);
        $this->assertNull($dto->getDtos()[0]->entity_id);
        $this->assertNull($dto->getDtos()[0]->group);
    }

    /** @test */
    public function success_few_row(): void
    {
        $data = self::data();
        $dto = TranslationsDTO::byRequestFromApp($data);

        $this->assertCount(4, $dto->getDtos());
        foreach ($dto->getDtos() as $dto){
            $this->assertTrue($dto instanceof TranslationDTO);
            $this->assertEquals($dto->text, $data[$dto->alias][$dto->lang]);
        }
    }

    /** @test */
    public function success_empty(): void
    {
        $dto = TranslationsDTO::byRequestFromApp([]);

        $this->assertEmpty($dto->getDtos());
    }

    public static function data(): array
    {
        return [
            "button" => [
                "en" => "button en",
                "ua" => "button ua"
            ],
            "text" => [
                "en" => "button en",
                "ua" => "button ua"
            ]
        ];
    }
}

