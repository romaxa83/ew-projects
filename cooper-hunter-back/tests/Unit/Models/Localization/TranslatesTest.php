<?php

namespace Tests\Unit\Models\Localization;

use App\Models\Localization\Translate;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TranslatesTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_create_translation_success()
    {
        $attributes = [
            'place' => 'site',
            'key' => 'translation slug',
            'text' => 'Translate text',
            'lang' => app('localization')->getDefaultSlug(),
        ];

        $this->assertDatabaseMissing(Translate::TABLE, $attributes);

        Translate::query()->create($attributes);

        $this->assertDatabaseHas(Translate::TABLE, $attributes);
    }

    public function test_translate_factory()
    {
        $attributes = [
            'key' => 'translation slug',
            'text' => 'Translate text',
            'lang' => app('localization')->getDefaultSlug(),
        ];

        $this->assertDatabaseMissing(Translate::TABLE, $attributes);

        Translate::factory()->create($attributes);

        $this->assertDatabaseHas(Translate::TABLE, $attributes);
    }
}
