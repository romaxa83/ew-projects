<?php

namespace Tests\Unit\Dto;

use App\Dto\SimpleTranslationDto;
use Tests\TestCase;

class SimpleTranslationDtoTest extends TestCase
{
    /** @test */
    public function success_fill_by_args()
    {
        $data = $this->data();

        $dto = SimpleTranslationDto::byArgs($data);

        $this->assertEquals($dto->getTitle(), $data['title']);
        $this->assertEquals($dto->getLanguage(), $data['language']);
        $this->assertEquals($dto->getDescription(), $data['description']);
    }

    /** @test */
    public function success_fill_by_args_without_desc()
    {
        $data = $this->data();
        unset($data['description']);

        $dto = SimpleTranslationDto::byArgs($data);

        $this->assertEquals($dto->getTitle(), $data['title']);
        $this->assertEquals($dto->getLanguage(), $data['language']);
        $this->assertNull($dto->getDescription());
    }

    public function data()
    {
        return [
            'language' => 'ru',
            'title' => 'some title',
            'description' => 'some description'
        ];
    }
}
