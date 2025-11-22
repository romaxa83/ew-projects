<?php

namespace Tests\Unit\DTO\Permission;

use App\DTO\NameTranslationDTO;
use App\DTO\Permission\RoleDTO;
use Tests\TestCase;

class RoleDTOTest extends TestCase
{
    /** @test */
    public function check_fill_by_args()
    {
        $data = [
            'name' => 'test',
            'translations' => [
                [
                    'lang' => 'ru',
                    'name' => 'test_ru'
                ],
                [
                    'lang' => 'uk',
                    'name' => 'test_uk'
                ],
            ]
        ];

        $dto = RoleDTO::byArgs($data);

        $this->assertEquals($dto->getName(), $data['name']);
        foreach ($dto->getTranslations() as $key => $translation){
            /** @var $translation NameTranslationDTO */
            $this->assertTrue($translation instanceof NameTranslationDTO);
            $this->assertEquals($translation->getName(), $data['translations'][$key]['name']);
            $this->assertEquals($translation->getLang(), $data['translations'][$key]['lang']);
        }
    }
}
