<?php

namespace Tests\Unit\DTO\Catalog\Service;

use App\DTO\Catalog\Service\DriverAgeDTO;
use App\DTO\NameTranslationDTO;
use Tests\TestCase;

class DriverAgeDTOTest extends TestCase
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
                    'name' => 'driver age ru'
                ],
                [
                    'lang' => 'uk',
                    'name' => 'driver age uk'
                ],
            ]
        ];

        $dto = DriverAgeDTO::byArgs($data);

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

        $dto = DriverAgeDTO::byArgs($data);

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

        DriverAgeDTO::byArgs($data);
    }
}

