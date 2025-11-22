<?php

namespace Tests\Unit\Dto\Catalog\Video;

use App\Dto\Catalog\Video\GroupDto;
use App\Dto\SimpleTranslationDto;
use App\Exceptions\AssertDataException;
use App\Models\Catalog\Videos\Group;
use Tests\TestCase;

class GroupDtoTest extends TestCase
{
    /** @test */
    public function success_fill_by_args()
    {
        $data = static::data();

        $dto = GroupDto::byArgs($data);

        $this->assertEquals($dto->getActive(), $data['active']);

        $this->assertNotEmpty($dto->getTranslations());
        $this->assertIsArray($dto->getTranslations());

        foreach ($dto->getTranslations() as $key => $translation){
            /** @var $translation SimpleTranslationDto */
            $this->assertTrue($translation instanceof SimpleTranslationDto);
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
    public function success_fill_by_args_without_active()
    {
        $data = static::data();
        unset($data['active']);

        $dto = GroupDto::byArgs($data);

        $this->assertEquals($dto->getActive(), Group::DEFAULT_ACTIVE);
    }

    /** @test */
    public function fail_without_translations()
    {
        $data = static::data();
        unset($data['translations']);

        $this->expectException(AssertDataException::class);
        $this->expectExceptionMessage(__('exceptions.assert_data.field must exist', ['field' => 'translations']));

        GroupDto::byArgs($data);
    }

    public static function data()
    {
        return [
            'sort' => 2,
            'active' => false,
            'translations' => [
                'es' => [
                    'language' => 'es',
                    'title' => 'some title group es',
                    'description' => 'some desc group es',
                ],
                'en' => [
                    'language' => 'en',
                    'title' => 'some title group en',
                    'description' => 'some desc group en',
                ]
            ]
        ];
    }
}

