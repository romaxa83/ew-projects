<?php

namespace Tests\Unit\DTO\Catalog\Car;

use App\DTO\Catalog\Car\TransportTypeDTO;
use App\DTO\NameTranslationDTO;
use Tests\TestCase;

class TransportTypeDTOTest extends TestCase
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
                    'name' => 'transport type ru'
                ],
                [
                    'lang' => 'uk',
                    'name' => 'transport type uk'
                ],
            ]
        ];

        $dto = TransportTypeDTO::byArgs($data);

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
                    'name' => 'service_ru'
                ],
                [
                    'lang' => 'uk',
                    'name' => 'service_uk'
                ],
            ]
        ];

        $dto = TransportTypeDTO::byArgs($data);

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

        TransportTypeDTO::byArgs($data);
    }
}

