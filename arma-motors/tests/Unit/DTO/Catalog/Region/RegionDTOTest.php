<?php

namespace Tests\Unit\DTO\Catalog\Region;

use App\DTO\Catalog\Region\RegionDTO;
use App\DTO\NameTranslationDTO;
use Tests\TestCase;

class RegionDTOTest extends TestCase
{
    /** @test */
    public function check_fill_by_args()
    {
        $data = [
            'id' => '1',
            'sort' => 1,
            'active' => true,
            'translations' => [
                [
                    'lang' => 'ru',
                    'name' => 'test_ru'
                ],
                [
                    'lang' => 'uk',
                    'name' => 'test_uk'
                ],
            ]
        ];

        $dto = RegionDTO::byArgs($data);

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
    public function check_fill_by_args_only_translation()
    {
        $data = [
            'translations' => [
                [
                    'lang' => 'ru',
                    'name' => 'test_ru'
                ],
                [
                    'lang' => 'uk',
                    'name' => 'test_uk'
                ],
            ]
        ];

        $dto = RegionDTO::byArgs($data);

        $this->assertNull($dto->getSort());
        $this->assertNull($dto->getActive());
        foreach ($dto->getTranslations() as $key => $translation){
            /** @var $translation NameTranslationDTO */
            $this->assertTrue($translation instanceof NameTranslationDTO);
            $this->assertEquals($translation->getName(), $data['translations'][$key]['name']);
            $this->assertEquals($translation->getLang(), $data['translations'][$key]['lang']);
        }
    }
}
