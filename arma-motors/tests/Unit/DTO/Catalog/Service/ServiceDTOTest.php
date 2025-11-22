<?php

namespace Tests\Unit\DTO\Catalog\Service;

use App\DTO\Catalog\Region\RegionDTO;
use App\DTO\Catalog\Service\ServiceDTO;
use App\DTO\NameTranslationDTO;
use Tests\TestCase;

class ServiceDTOTest extends TestCase
{
    /** @test */
    public function check_fill_by_args()
    {
        $data = [
            'sort' => 1,
            'active' => false,
            'parentId' => 1,
            'alias' => 'some',
            'icon' => 'some icon',
            'forGuest' => true,
            'timeStep' => 30,
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

        $dto = ServiceDTO::byArgs($data);

        $this->assertEquals($dto->getSort(), $data['sort']);
        $this->assertEquals($dto->getActive(), $data['active']);
        $this->assertEquals($dto->getParentID(), $data['parentId']);
        $this->assertEquals($dto->getAlias(), $data['alias']);
        $this->assertEquals($dto->getIcon(), $data['icon']);
        $this->assertEquals($dto->getForGuest(), $data['forGuest']);
        $this->assertEquals($dto->getTimeStep(), $data['timeStep']);
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
            'alias' => 'some',
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

        $dto = ServiceDTO::byArgs($data);

        $this->assertEquals($dto->getSort(), 0);
        $this->assertEquals($dto->getTimeStep(), 0);
        $this->assertTrue($dto->getActive());
        $this->assertFalse($dto->getForGuest());
        $this->assertNull($dto->getParentID());
        $this->assertNull($dto->getIcon());
        $this->assertEquals($dto->getAlias(), $data['alias']);
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

        ServiceDTO::byArgs($data);
    }

    /** @test */
    public function fail_without_translation()
    {
        $data = [
            'alias' => 'some_alias'
        ];

        $this->expectException(\InvalidArgumentException::class);

        ServiceDTO::byArgs($data);
    }
}
