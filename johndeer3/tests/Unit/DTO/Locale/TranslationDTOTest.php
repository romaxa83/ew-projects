<?php

namespace Tests\Unit\DTO\Locale;

use App\DTO\Locale\TranslationDTO;
use App\Models\Translate;
use PHPUnit\Framework\TestCase;

class TranslationDTOTest extends TestCase
{
    /** @test */
    public function success(): void
    {
        $data = self::data();
        $dto = TranslationDTO::byArgs($data);

        $this->assertEquals($dto->model, $data['model']);
        $this->assertEquals($dto->entity_type, $data['entity_type']);
        $this->assertEquals($dto->entity_id, $data['entity_id']);
        $this->assertEquals($dto->text, $data['text']);
        $this->assertEquals($dto->lang, $data['lang']);
        $this->assertEquals($dto->alias, $data['alias']);
        $this->assertEquals($dto->group, $data['group']);
    }

    /** @test */
    public function success_empty(): void
    {
        $dto = TranslationDTO::byArgs([]);

        $this->assertNull($dto->model);
        $this->assertNull($dto->entity_type);
        $this->assertNull($dto->entity_id);
        $this->assertNull($dto->text);
        $this->assertNull($dto->lang);
        $this->assertNull($dto->alias);
        $this->assertNull($dto->group);
    }

    public static function data(): array
    {
        return [
            'model' => Translate::TYPE_SITE,
            'entity_type' => 'App\Models\Test',
            'entity_id' => 1,
            'text' => 'some translation',
            'lang' => 'en',
            'alias' => 'some alias',
            'group' => 'some group',
        ];
    }
}
