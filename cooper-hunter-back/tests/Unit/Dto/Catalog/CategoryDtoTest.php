<?php

namespace Tests\Unit\Dto\Catalog;

use App\Dto\Catalog\CategoryDto;
use App\Dto\SimpleTranslationDto;
use App\Exceptions\AssertDataException;
use App\Models\Catalog\Categories\Category;
use Str;
use Tests\TestCase;

class CategoryDtoTest extends TestCase
{
    /** @test */
    public function success_fill_by_args(): void
    {
        $data = static::data();

        $dto = CategoryDto::byArgs($data);

        $this->assertEquals($dto->getActive(), $data['active']);
        $this->assertEquals($dto->getParentId(), $data['parent_id']);

        $this->assertNotEmpty($dto->getTranslations());
        $this->assertIsArray($dto->getTranslations());

        foreach ($dto->getTranslations() as $translation) {
            /** @var $translation SimpleTranslationDto */
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

    public static function data(): array
    {
        return [
            'sort' => 2,
            'active' => false,
            'parent_id' => 22,
            'slug' => Str::random(10),
            'translations' => [
                'es' => [
                    'language' => 'es',
                    'title' => 'some title es',
                    'description' => 'some desc es',
                    'seo_title' => 'custom seo title es',
                    'seo_description' => 'custom seo description es',
                    'seo_h1' => 'custom seo h1 es',
                ],
                'en' => [
                    'language' => 'en',
                    'title' => 'some title en',
                    'description' => 'some desc en',
                    'seo_title' => 'custom seo title en',
                    'seo_description' => 'custom seo description en',
                    'seo_h1' => 'custom seo h1 en',
                ]
            ]
        ];
    }

    /** @test */
    public function success_fill_by_args_without_active(): void
    {
        $data = static::data();
        unset($data['active']);

        $dto = CategoryDto::byArgs($data);

        $this->assertEquals(Category::DEFAULT_ACTIVE, $dto->getActive());
    }

    /** @test */
    public function success_fill_by_args_without_parent_id(): void
    {
        $data = static::data();
        unset($data['parent_id']);

        $dto = CategoryDto::byArgs($data);

        $this->assertNull($dto->getParentId());
    }

    /** @test */
    public function fail_without_translations(): void
    {
        $data = static::data();
        unset($data['translations']);

        $this->expectException(AssertDataException::class);
        $this->expectExceptionMessage(__('exceptions.assert_data.field must exist', ['field' => 'translations']));

        CategoryDto::byArgs($data);
    }
}

