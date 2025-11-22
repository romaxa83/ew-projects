<?php

namespace Tests\Unit\DTO\Catalog\Calc;

use App\DTO\Catalog\Calc\SparesGroupEditDTO;
use App\DTO\Catalog\Calc\SparesGroupTranslationDTO;
use App\Models\Catalogs\Calc\SparesGroup;
use Tests\TestCase;

class SparesGroupEditDTOTest extends TestCase
{
    /** @test */
    public function check_fill_by_args()
    {
        $data = [
            'sort' => 1,
            'active' => true,
            'type' => SparesGroup::TYPE_QTY,
            'brandId' => 1,
            'translations' => [
                'ru' => [
                    'lang' => 'ru',
                    'name' => 'transmission edit ru',
                    'unit' => 'tr'
                ],
                'uk' => [
                    'lang' => 'uk',
                    'name' => 'transmission edit uk',
                    'unit' => 'tr'
                ]
            ]
        ];

        $dto = SparesGroupEditDTO::byArgs($data);

        $this->assertEquals($dto->getSort(), $data['sort']);
        $this->assertEquals($dto->getActive(), $data['active']);
        $this->assertEquals($dto->getType(), $data['type']);
        $this->assertEquals($dto->getBrandId(), $data['brandId']);

        $this->assertTrue($dto->changeActive());
        $this->assertTrue($dto->changeSort());
        $this->assertTrue($dto->changeType());
        $this->assertTrue($dto->changeBrandId());
        $this->assertTrue($dto->hasTranslations());
        foreach ($dto->getTranslations() as $key => $translation){
            /** @var $translation SparesGroupTranslationDTO */
            $this->assertTrue($translation instanceof SparesGroupTranslationDTO);
            $this->assertEquals($translation->getName(), $data['translations'][$translation->getLang()]['name']);
            $this->assertEquals($translation->getLang(), $data['translations'][$translation->getLang()]['lang']);
            $this->assertEquals($translation->getUnit(), $data['translations'][$translation->getLang()]['unit']);
        }
    }

    /** @test */
    public function check_empty_by_args()
    {
        $data = [];

        $dto = SparesGroupEditDTO::byArgs($data);

        $this->assertFalse($dto->hasTranslations());
        $this->assertEmpty($dto->getTranslations());
        $this->assertFalse($dto->changeActive());
        $this->assertFalse($dto->changeSort());
        $this->assertFalse($dto->changeType());
        $this->assertFalse($dto->changeBrandId());
        $this->assertNull($dto->getSort());
        $this->assertNull($dto->getActive());
        $this->assertNull($dto->getType());
        $this->assertNull($dto->getBrandId());
    }

    /** @test */
    public function check_some_field_by_args()
    {
        $data = [
            'sort' => 1,
            'active' => true,
        ];

        $dto = SparesGroupEditDTO::byArgs($data);

        $this->assertFalse($dto->hasTranslations());
        $this->assertEmpty($dto->getTranslations());

        $this->assertTrue($dto->changeActive());
        $this->assertTrue($dto->changeSort());
        $this->assertFalse($dto->changeType());
        $this->assertFalse($dto->changeBrandId());
        $this->assertNotNull($dto->getSort());
        $this->assertNotNull($dto->getActive());
        $this->assertNull($dto->getType());
        $this->assertNull($dto->getBrandId());
    }
}
