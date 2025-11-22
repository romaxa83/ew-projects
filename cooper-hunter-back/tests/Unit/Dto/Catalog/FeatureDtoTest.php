<?php

namespace Tests\Unit\Dto\Catalog;

use App\Dto\Catalog\FeatureDto;
use App\Dto\SimpleTranslationDto;
use App\Models\Catalog\Features\Value;
use Tests\TestCase;

class FeatureDtoTest extends TestCase
{
    /** @test */
    public function success_fill_by_args(): void
    {
        $data = static::data();

        $dto = FeatureDto::byArgs($data);

        $this->assertEquals($dto->getActive(), $data['active']);

        $this->assertNotEmpty($dto->getTranslations());
        $this->assertIsArray($dto->getTranslations());

        foreach ($dto->getTranslations() as $key => $translation) {
            $this->assertInstanceOf(SimpleTranslationDto::class, $translation);
            $this->assertEquals($translation->getTitle(), $data['translations'][$translation->getLanguage()]['title']);
            $this->assertEquals(
                $translation->getDescription(),
                $data['translations'][$translation->getLanguage()]['description']
            );
            $this->assertEquals(
                $translation->getLanguage(),
                $data['translations'][$translation->getLanguage()]['language']
            );
        }
    }

    /** @test */
    public function success_fill_by_args_without_active(): void
    {
        $data = static::data();
        unset($data['active']);

        $dto = FeatureDto::byArgs($data);

        $this->assertEquals(Value::DEFAULT_ACTIVE, $dto->getActive());
    }

    public static function data(): array
    {
        return [
            'sort' => 2,
            'active' => false,
            'translations' => [
                'es' => [
                    'language' => 'es',
                    'title' => 'some value title es',
                    'description' => 'some desc es',
                ],
                'en' => [
                    'language' => 'en',
                    'title' => 'some value title en',
                    'description' => 'some desc en',
                ]
            ]
        ];
    }
}

