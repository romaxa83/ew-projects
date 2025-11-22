<?php

namespace Tests\Unit\Dto\Catalog\Video;

use App\Dto\Catalog\Video\LinkDto;
use App\Dto\SimpleTranslationDto;
use App\Enums\Catalog\Videos\VideoLinkTypeEnum;
use App\Exceptions\AssertDataException;
use App\Models\Catalog\Videos\VideoLink;
use Tests\TestCase;

class LinkDtoTest extends TestCase
{
    /** @test */
    public function success_fill_by_args()
    {
        $data = static::data();

        $dto = LinkDto::byArgs($data);

        $this->assertEquals($dto->getActive(), $data['active']);
        $this->assertEquals($dto->getGroupId(), $data['group_id']);
        $this->assertEquals($dto->getLink(), $data['link']);

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

        $dto = LinkDto::byArgs($data);

        $this->assertEquals($dto->getActive(), VideoLink::DEFAULT_ACTIVE);
    }

    /** @test */
    public function fail_without_group_id()
    {
        $data = static::data();
        unset($data['group_id']);

        $this->expectException(AssertDataException::class);
        $this->expectExceptionMessage(__('exceptions.assert_data.field must exist', ['field' => 'group_id']));

        LinkDto::byArgs($data);
    }

    /** @test */
    public function fail_without_link()
    {
        $data = static::data();
        unset($data['link']);

        $this->expectException(AssertDataException::class);
        $this->expectExceptionMessage(__('exceptions.assert_data.field must exist', ['field' => 'link']));

        LinkDto::byArgs($data);
    }

    /** @test */
    public function fail_without_translations()
    {
        $data = static::data();
        unset($data['translations']);

        $this->expectException(AssertDataException::class);
        $this->expectExceptionMessage(__('exceptions.assert_data.field must exist', ['field' => 'translations']));

        LinkDto::byArgs($data);
    }


    public static function data()
    {
        return [
            'sort' => 2,
            'active' => false,
            'group_id' => 22,
            'link_type' => VideoLinkTypeEnum::COMMON,
            'link' => 'https://youtube.com/bla-bla',
            'translations' => [
                'es' => [
                    'language' => 'es',
                    'title' => 'some title link es',
                    'description' => 'some desc link es',
                ],
                'en' => [
                    'language' => 'en',
                    'title' => 'some title link en',
                    'description' => 'some desc link en',
                ]
            ]
        ];
    }
}

