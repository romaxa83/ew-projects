<?php

namespace Tests\Unit\DTO\Support;

use App\DTO\NameTranslationDTO;
use App\DTO\Support\SupportCategoryDTO;
use Tests\TestCase;

class SupportCategoryDTOTest extends TestCase
{
    /** @test */
    public function check_fill_by_args()
    {
        $data = [
            'sort' => 1,
            'active' => false,
            'translations' => [
                [
                    'lang' => 'ru',
                    'name' => 'category ru'
                ],
                [
                    'lang' => 'uk',
                    'name' => 'category uk'
                ],
            ]
        ];

        $dto = SupportCategoryDTO::byArgs($data);

        $this->assertEquals($dto->getSort(), $data['sort']);
        $this->assertEquals($dto->getActive(), $data['active']);
        foreach ($dto->getTranslations() as $key => $translation){
            /** @var $translation NameTranslationDTO */
            $this->assertTrue($translation instanceof NameTranslationDTO);
            $this->assertEquals($translation->getName(), $data['translations'][$key]['name']);
            $this->assertEquals($translation->getLang(), $data['translations'][$key]['lang']);
        }
    }

    /** @test */
    public function check_required_fields()
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

        $dto = SupportCategoryDTO::byArgs($data);

        $this->assertEquals($dto->getSort(), 0);
        $this->assertTrue($dto->getActive());
        foreach ($dto->getTranslations() as $key => $translation){
            /** @var $translation NameTranslationDTO */
            $this->assertTrue($translation instanceof NameTranslationDTO);
            $this->assertEquals($translation->getName(), $data['translations'][$key]['name']);
            $this->assertEquals($translation->getLang(), $data['translations'][$key]['lang']);
        }
    }

    /** @test */
    public function fail_empty()
    {
        $data = [];

        $this->expectException(\InvalidArgumentException::class);

        SupportCategoryDTO::byArgs($data);
    }
}


