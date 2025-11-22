<?php

namespace Tests\Unit\DTO\Catalog\Service;

use App\DTO\Catalog\Service\DurationDTO;
use App\DTO\NameTranslationDTO;
use Tests\TestCase;

class DurationDTOTest extends TestCase
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
                    'name' => 'duration ru'
                ],
                [
                    'lang' => 'uk',
                    'name' => 'duration uk'
                ],
            ],
            'serviceIds' => [1, 2]
        ];

        $dto = DurationDTO::byArgs($data);

        $this->assertEquals($dto->getSort(), $data['sort']);
        $this->assertEquals($dto->getActive(), $data['active']);
        $this->assertNotEmpty($dto->getServiceIds());
        $this->assertIsArray($dto->getServiceIds());
        $this->assertFalse($dto->emptyServiceIds());
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

        $dto = DurationDTO::byArgs($data);

        $this->assertEquals($dto->getSort(), 0);
        $this->assertTrue($dto->getActive());
        $this->assertEmpty($dto->getServiceIds());
        $this->assertTrue($dto->emptyServiceIds());
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

        DurationDTO::byArgs($data);
    }
}
