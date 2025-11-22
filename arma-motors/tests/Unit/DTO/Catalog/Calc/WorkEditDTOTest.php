<?php

namespace Tests\Unit\DTO\Catalog\Calc;

use App\DTO\Catalog\Calc\WorkEditDTO;
use App\DTO\NameTranslationDTO;
use Tests\TestCase;

class WorkEditDTOTest extends TestCase
{
    /** @test */
    public function check_fill_by_args()
    {
        $data = [
            'sort' => 1,
            'active' => true,
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

        $dto = WorkEditDTO::byArgs($data);

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

        $dto = WorkEditDTO::byArgs($data);

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

        $dto = WorkEditDTO::byArgs($data);

        $this->assertFalse($dto->hasTranslations());
        $this->assertEmpty($dto->getTranslations());

        $this->assertTrue($dto->changeActive());
        $this->assertTrue($dto->changeSort());
        $this->assertNotNull($dto->getSort());
        $this->assertNotNull($dto->getActive());
    }
}
