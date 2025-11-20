<?php

namespace Tests\Unit\DTO\Locale;

use App\DTO\Locale\LanguageDTO;
use PHPUnit\Framework\TestCase;

class LanguageDTOTest extends TestCase
{
    /** @test */
    public function success(): void
    {
        $data = self::data();
        $dto = LanguageDTO::byArgs($data);

        $this->assertEquals($dto->name, $data['name']);
        $this->assertEquals($dto->native, $data['native']);
        $this->assertEquals($dto->slug, $data['slug']);
        $this->assertEquals($dto->locale, $data['locale']);
        $this->assertEquals($dto->default, $data['default']);
    }

    /** @test */
    public function success_only_required(): void
    {
        $data = self::data();
        unset(
            $data['native'],
            $data['default']
        );

        $dto = LanguageDTO::byArgs($data);

        $this->assertNull($dto->native);
        $this->assertFalse($dto->default);
    }

    public static function data(): array
    {
        return [
            'name' => 'English',
            'native' => 'English',
            'slug' => 'en',
            'locale' => 'en_EN',
            'default' => true
        ];
    }
}

