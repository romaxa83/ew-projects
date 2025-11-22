<?php

namespace Tests\Unit\DTO\Promotion;

use App\DTO\NameTranslationDTO;
use App\DTO\Promotion\PromotionDTO;
use App\DTO\Support\SupportCategoryDTO;
use Tests\TestCase;

class PromotionDTOTest extends TestCase
{
    /** @test */
    public function check_fill_by_args()
    {
        $data = [
            'sort' => 1,
            'active' => false,
            'type' => 'common',
            'departmentId' => '1',
            'link' => 'some link',
            'startAt' => '1624350625',
            'finishAt' => '17868756',
            'translations' => [
                [
                    'lang' => 'ru',
                    'name' => 'promotion name ru',
                    'text' => 'promotion text ru'
                ],
                [
                    'lang' => 'uk',
                    'name' => 'promotion name uk',
                    'text' => 'promotion text uk'
                ],
            ],
            'userIds' => [1,23,3]
        ];

        $dto = PromotionDTO::byArgs($data);

        $this->assertEquals($dto->getSort(), $data['sort']);
        $this->assertEquals($dto->getActive(), $data['active']);
        $this->assertEquals($dto->getType(), $data['type']);
        $this->assertEquals($dto->getDepartmentId(), $data['departmentId']);
        $this->assertEquals($dto->getStartAt(), $data['startAt']);
        $this->assertEquals($dto->getFinishAt(), $data['finishAt']);
        $this->assertEquals($dto->getLink(), $data['link']);
        $this->assertFalse($dto->emptyUserIds());
        $this->assertIsArray($dto->getUserIds());
        foreach ($dto->getTranslations() as $key => $translation){
            /** @var $translation NameTranslationDTO */
            $this->assertTrue($translation instanceof NameTranslationDTO);
            $this->assertEquals($translation->getName(), $data['translations'][$key]['name']);
            $this->assertEquals($translation->getLang(), $data['translations'][$key]['lang']);
            $this->assertEquals($translation->getText(), $data['translations'][$key]['text']);
        }
    }

    /** @test */
    public function check_required_fields()
    {
        $data = [
            'type' => 'common',
            'departmentId' => '1',
            'startAt' => '17868756565',
            'finishAt' => '17868756563',
            'translations' => [
                [
                    'lang' => 'ru',
                    'name' => 'promotion name ru',
                    'text' => 'promotion text ru'
                ],
                [
                    'lang' => 'uk',
                    'name' => 'promotion name uk',
                    'text' => 'promotion text uk'
                ],
            ]
        ];

        $dto = PromotionDTO::byArgs($data);

        $this->assertEquals($dto->getSort(), 0);
        $this->assertTrue($dto->getActive());
        $this->assertTrue($dto->emptyUserIds());
        $this->assertIsArray($dto->getUserIds());
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

        PromotionDTO::byArgs($data);
    }
}



