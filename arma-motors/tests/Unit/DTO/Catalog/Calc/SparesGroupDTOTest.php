<?php

namespace Tests\Unit\DTO\Catalog\Calc;

use App\DTO\Catalog\Calc\SparesGroupDTO;
use App\DTO\Catalog\Calc\SparesGroupTranslationDTO;
use App\Models\Catalogs\Calc\SparesGroup;
use Tests\TestCase;

class SparesGroupDTOTest extends TestCase
{
    /** @test */
    public function check_fill_by_args()
    {
        $data = [
            'sort' => 1,
            'active' => false,
            'type' => SparesGroup::TYPE_QTY,
            'brandId' => 1,
            'translations' => [
                [
                    'lang' => 'ru',
                    'name' => 'spares group ru',
                    'unit' => 'gr'
                ],
                [
                    'lang' => 'uk',
                    'name' => 'spares group uk',
                    'unit' => 'gr'
                ],
            ]
        ];

        $dto = SparesGroupDTO::byArgs($data);

        $this->assertEquals($dto->getSort(), $data['sort']);
        $this->assertEquals($dto->getActive(), $data['active']);
        $this->assertEquals($dto->getType(), $data['type']);
        $this->assertEquals($dto->getBrandId(), $data['brandId']);
        foreach ($dto->getTranslations() as $key => $translation){
            /** @var $translation SparesGroupTranslationDTO */
            $this->assertTrue($translation instanceof SparesGroupTranslationDTO);
            $this->assertEquals($translation->getName(), $data['translations'][$key]['name']);
            $this->assertEquals($translation->getLang(), $data['translations'][$key]['lang']);
            $this->assertEquals($translation->getUnit(), $data['translations'][$key]['unit']);
        }
    }

    /** @test */
    public function check_required_fields()
    {
        $data = [
            'type' => SparesGroup::TYPE_QTY,
            'translations' => [
                [
                    'lang' => 'ru',
                    'name' => 'service_ru',
                    'unit' => 'gr'
                ],
                [
                    'lang' => 'uk',
                    'name' => 'service_uk',
                    'unit' => 'gr'
                ],
            ]
        ];

        $dto = SparesGroupDTO::byArgs($data);

        $this->assertEquals($dto->getSort(), 0);
        $this->assertTrue($dto->getActive());
        $this->assertEquals($dto->getType(), $data['type']);
        $this->assertNull($dto->getBrandId());
        foreach ($dto->getTranslations() as $key => $translation){
            /** @var $translation SparesGroupTranslationDTO */
            $this->assertTrue($translation instanceof SparesGroupTranslationDTO);
            $this->assertEquals($translation->getName(), $data['translations'][$key]['name']);
            $this->assertEquals($translation->getLang(), $data['translations'][$key]['lang']);
            $this->assertEquals($translation->getUnit(), $data['translations'][$key]['unit']);
        }
    }

    /** @test */
    public function fail_empty()
    {
        $data = [];

        $this->expectException(\InvalidArgumentException::class);

        SparesGroupDTO::byArgs($data);
    }

    /** @test */
    public function without_type()
    {
        $data = [
            'translations' => [
                [
                    'lang' => 'ru',
                    'name' => 'service_ru',
                    'unit' => 'gr'
                ],
                [
                    'lang' => 'uk',
                    'name' => 'service_uk',
                    'unit' => 'gr'
                ],
            ]
        ];

        $this->expectException(\InvalidArgumentException::class);

        SparesGroupDTO::byArgs($data);
    }

    /** @test */
    public function without_unit()
    {
        $data = [
            'type' => SparesGroup::TYPE_QTY,
            'translations' => [
                [
                    'lang' => 'ru',
                    'name' => 'service_ru',
                ],
                [
                    'lang' => 'uk',
                    'name' => 'service_uk',
                ],
            ]
        ];

        $this->expectException(\InvalidArgumentException::class);

        SparesGroupDTO::byArgs($data);
    }
}
