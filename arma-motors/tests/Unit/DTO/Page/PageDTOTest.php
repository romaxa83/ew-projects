<?php

namespace Tests\Unit\DTO\Page;

use App\DTO\Page\PageDTO;
use App\DTO\Page\PageTranslationDTO;
use Tests\TestCase;

class PageDTOTest extends TestCase
{
    /** @test */
    public function check_fill_by_args()
    {
        $data = [
            'alias' => 'some',
            'translations' => [
                [
                    'lang' => 'ru',
                    'name' => 'page ru',
                    'text' => 'page text ru',
                    'subText' => 'page sub text ru'
                ],
                [
                    'lang' => 'uk',
                    'name' => 'page uk',
                    'text' => 'page text uk',
                    'subText' => 'page sub text uk'
                ],
            ]
        ];

        $dto = PageDTO::byArgs($data);

        $this->assertEquals($dto->getAlias(), $data['alias']);
        foreach ($dto->getTranslations() as $key => $translation){
            /** @var $translation PageTranslationDTO */
            $this->assertTrue($translation instanceof PageTranslationDTO);
            $this->assertEquals($translation->getName(), $data['translations'][$key]['name']);
            $this->assertEquals($translation->getLang(), $data['translations'][$key]['lang']);
            $this->assertEquals($translation->getText(), $data['translations'][$key]['text']);
            $this->assertEquals($translation->getSubText(), $data['translations'][$key]['subText']);
        }
    }

    /** @test */
    public function without_sub_text()
    {
        $data = [
            'alias' => 'some',
            'translations' => [
                [
                    'lang' => 'ru',
                    'name' => 'page ru',
                    'text' => 'page text ru'
                ],
                [
                    'lang' => 'uk',
                    'name' => 'page uk',
                    'text' => 'page text uk'
                ],
            ]
        ];

        $dto = PageDTO::byArgs($data);

        $this->assertEquals($dto->getAlias(), $data['alias']);
        foreach ($dto->getTranslations() as $key => $translation){
            /** @var $translation PageTranslationDTO */
            $this->assertTrue($translation instanceof PageTranslationDTO);
            $this->assertEquals($translation->getName(), $data['translations'][$key]['name']);
            $this->assertEquals($translation->getLang(), $data['translations'][$key]['lang']);
            $this->assertEquals($translation->getText(), $data['translations'][$key]['text']);
            $this->assertNull($translation->getSubText());
        }
    }

    /** @test */
    public function without_alias()
    {
        $data = [
            'translations' => [
                [
                    'lang' => 'ru',
                    'name' => 'driver age ru'
                ],
                [
                    'lang' => 'uk',
                    'name' => 'driver age uk'
                ],
            ]
        ];

        $this->expectException(\InvalidArgumentException::class);
        PageDTO::byArgs($data);
    }

    /** @test */
    public function without_translation()
    {
        $data = [
            'alias' => 'alias'
        ];

        $this->expectException(\InvalidArgumentException::class);
        PageDTO::byArgs($data);
    }
}
