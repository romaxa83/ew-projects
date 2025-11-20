<?php

namespace Tests\Unit\Models\Localization;

use App\Models\Localization\Translation;
use Tests\TestCase;

class TranslatesTest extends TestCase
{
    public function test_it_create_translation_success(): void
    {
        $attributes = [
            'place' => 'site',
            'key' => 'translate slug',
            'text' => 'Translate text',
            'lang' => app('localization')->getDefaultSlug(),
        ];

        $this->assertDatabaseMissing(Translation::TABLE, $attributes);

        Translation::query()->create($attributes);

        $this->assertDatabaseHas(Translation::TABLE, $attributes);
    }

    public function test_translate_factory(): void
    {
        $attributes = [
            'key' => 'translate slug',
            'text' => 'Translate text',
            'lang' => app('localization')->getDefaultSlug(),
        ];

        $this->assertDatabaseMissing(Translation::TABLE, $attributes);

        Translation::factory()->create($attributes);

        $this->assertDatabaseHas(Translation::TABLE, $attributes);
    }
}
