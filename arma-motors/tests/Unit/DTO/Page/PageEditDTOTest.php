<?php

namespace Tests\Unit\DTO\Page;

use App\DTO\Page\PageEditDTO;
use App\DTO\Page\PageTranslationDTO;
use Tests\TestCase;

class PageEditDTOTest extends TestCase
{
    /** @test */
    public function check_fill_by_args()
    {
        $data = [
            'translations' => [
                'ru' => [
                    'lang' => 'ru',
                    'name' => 'driver age ru'
                ],
                'uk' => [
                    'lang' => 'uk',
                    'name' => 'driver age uk'
                ]
            ]
        ];

        $dto = PageEditDTO::byArgs($data);

        foreach ($dto->getTranslations() as $key => $translation){
            /** @var $translation PageTranslationDTO */
            $this->assertTrue($translation instanceof PageTranslationDTO);
            $this->assertEquals($translation->getName(), $data['translations'][$translation->getLang()]['name']);
            $this->assertEquals($translation->getLang(), $data['translations'][$translation->getLang()]['lang']);
            $this->assertNull($translation->getText());
            $this->assertNull($translation->getSubText());
        }
    }

    /** @test */
    public function check_empty()
    {
        $data = [];

        $this->expectException(\InvalidArgumentException::class);
        $dto =  PageEditDTO::byArgs($data);
    }
}



