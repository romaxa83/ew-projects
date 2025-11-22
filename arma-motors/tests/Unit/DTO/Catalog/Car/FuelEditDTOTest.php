<?php

namespace Tests\Unit\DTO\Catalog\Car;

use App\DTO\Catalog\Calc\WorkEditDTO;
use App\DTO\Catalog\Car\FuelEditDTO;
use App\DTO\NameTranslationDTO;
use Tests\TestCase;

class FuelEditDTOTest extends TestCase
{
    /** @test */
    public function check_fill_by_args()
    {
        $data = [
            'sort' => 1,
            'active' => true,
            'minutes' => 100,
            'translations' => [
                'ru' => [
                    'lang' => 'ru',
                    'name' => 'transmission edit ru'
                ],
                'uk' => [
                    'lang' => 'uk',
                    'name' => 'transmission edit uk'
                ]
            ]
        ];

        $dto = FuelEditDTO::byArgs($data);

        $this->assertEquals($dto->getSort(), $data['sort']);
        $this->assertEquals($dto->getActive(), $data['active']);

        $this->assertTrue($dto->changeActive());
        $this->assertTrue($dto->changeSort());
        $this->assertTrue($dto->hasTranslations());
        foreach ($dto->getTranslations() as $key => $translation){
            /** @var $translation NameTranslationDTO */
            $this->assertTrue($translation instanceof NameTranslationDTO);
            $this->assertEquals($translation->getName(), $data['translations'][$translation->getLang()]['name']);
            $this->assertEquals($translation->getLang(), $data['translations'][$translation->getLang()]['lang']);
        }
    }

    /** @test */
    public function check_empty_by_args()
    {
        $data = [];

        $dto = FuelEditDTO::byArgs($data);

        $this->assertFalse($dto->hasTranslations());
        $this->assertEmpty($dto->getTranslations());
        $this->assertFalse($dto->changeActive());
        $this->assertFalse($dto->changeSort());
        $this->assertNull($dto->getSort());
        $this->assertNull($dto->getActive());
    }

    /** @test */
    public function check_some_field_by_args()
    {
        $data = [
            'sort' => 1,
            'active' => true,
        ];

        $dto = FuelEditDTO::byArgs($data);

        $this->assertFalse($dto->hasTranslations());
        $this->assertEmpty($dto->getTranslations());

        $this->assertTrue($dto->changeActive());
        $this->assertTrue($dto->changeSort());
        $this->assertNotNull($dto->getSort());
        $this->assertNotNull($dto->getActive());
    }
}
