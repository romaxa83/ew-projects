<?php

namespace Tests\Unit\DTO\Catalog\Service;

use App\DTO\Catalog\Service\ServiceEditDTO;
use App\DTO\NameTranslationDTO;
use Tests\TestCase;

class ServiceEditDTOTest extends TestCase
{
    /** @test */
    public function check_fill_by_args()
    {
        $data = [
            'parentId' => 1,
            'sort' => 1,
            'active' => true,
            'timeStep' => 60,
            'translations' => [
                'ru' => [
                    'lang' => 'ru',
                    'name' => 'some ru'
                ],
                'uk' => [
                    'lang' => 'uk',
                    'name' => 'some uk'
                ]
            ]
        ];

        $dto = ServiceEditDTO::byArgs($data);

        $this->assertEquals($dto->getSort(), $data['sort']);
        $this->assertEquals($dto->getTimeStep(), $data['timeStep']);
        $this->assertEquals($dto->getActive(), $data['active']);
        $this->assertEquals($dto->getParentId(), $data['parentId']);

        $this->assertTrue($dto->changeParentId());
        $this->assertTrue($dto->changeActive());
        $this->assertTrue($dto->changeSort());
        $this->assertTrue($dto->changeTimeStep());
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

        $dto = ServiceEditDTO::byArgs($data);

        $this->assertFalse($dto->hasTranslations());
        $this->assertEmpty($dto->getTranslations());
        $this->assertFalse($dto->changeParentId());
        $this->assertFalse($dto->changeActive());
        $this->assertFalse($dto->changeSort());
        $this->assertFalse($dto->changeTimeStep());
        $this->assertNull($dto->getSort());
        $this->assertNull($dto->getTimeStep());
        $this->assertNull($dto->getActive());
        $this->assertNull($dto->getParentId());
    }

    /** @test */
    public function check_some_field_by_args()
    {
        $data = [
            'sort' => 1,
            'active' => true,
        ];

        $dto = ServiceEditDTO::byArgs($data);

        $this->assertFalse($dto->hasTranslations());
        $this->assertEmpty($dto->getTranslations());

        $this->assertFalse($dto->changeParentId());
        $this->assertFalse($dto->changeTimeStep());
        $this->assertTrue($dto->changeActive());
        $this->assertTrue($dto->changeSort());
        $this->assertNotNull($dto->getSort());
        $this->assertNotNull($dto->getActive());
        $this->assertNull($dto->getParentId());
        $this->assertNull($dto->getTimeStep());
    }
}


