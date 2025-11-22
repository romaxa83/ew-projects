<?php

namespace Tests\Feature\Saas\Translates;

use App\Models\Translates\Translate;
use App\Models\Translates\TranslateTranslates;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TranslateCreateTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    /** @test */
    public function it_create_new_translate_success()
    {
        $this->loginAsSaasSuperAdmin();

        $key = $this->faker->unique()->slug;

        $model = ['key' => $key];
        $translate = [
            'language' => 'en',
            'text' => $key,
        ];

        $postData = $model
            + [
                'en' => ['text' => $key],
                'ru' => ['text' => $key],
                'es' => ['text' => $key],
            ];

        $this->assertDatabaseMissing(Translate::TABLE_NAME, $model);
        $this->assertDatabaseMissing(TranslateTranslates::TABLE_NAME, $translate);

        $this->postJson(route('v1.saas.translates.store'), $model + $postData)
            ->assertCreated();

        $this->assertDatabaseHas(Translate::TABLE_NAME, $model);
        $this->assertDatabaseHas(TranslateTranslates::TABLE_NAME, $translate);
    }
}
