<?php


namespace Tests\Feature\Saas\Translates;


use App\Models\Translates\Translate;
use App\Models\Translates\TranslateTranslates;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TranslateUpdateTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function test_it_update_translate_success()
    {
        $this->withoutExceptionHandling();

        $key = $this->faker->unique()->slug;

        $translate = factory(Translate::class)
            ->create(['key' => $key]);

        $translateEn = factory(TranslateTranslates::class)->create(
            [
                'row_id' => $translate->id,
                'language' => 'en',
            ]
        );
        factory(TranslateTranslates::class)->create(
            [
                'row_id' => $translate->id,
                'language' => 'ru',
            ]
        );
        factory(TranslateTranslates::class)->create(
            [
                'row_id' => $translate->id,
                'language' => 'es',
            ]
        );
        factory(TranslateTranslates::class)->create(
            [
                'row_id' => $translate->id,
                'language' => 'uk',
            ]
        );

        $this->loginAsSaasSuperAdmin();

        $sentence = $this->faker->unique()->slug;

        $modelEuUpdate = [
            'row_id' => $translateEn->row_id,
            'text' => $sentence,
            'language' => 'en',
        ];

        $modelRuUpdate = [
            'row_id' => $translateEn->row_id,
            'text' => $sentence,
            'language' => 'ru',
        ];

        $modelEsUpdate = [
            'row_id' => $translateEn->row_id,
            'text' => $sentence,
            'language' => 'es',
        ];

        $modelUkUpdate = [
            'row_id' => $translateEn->row_id,
            'text' => $sentence,
            'language' => 'uk',
        ];

        $postData = ['key' => $translate->key]
            + [
                'en' => ['text' => $sentence],
                'ru' => ['text' => $sentence],
                'es' => ['text' => $sentence],
                'uk' => ['text' => $sentence],
            ];

        $this->assertDatabaseMissing(TranslateTranslates::TABLE_NAME, $modelEuUpdate);
        $this->assertDatabaseMissing(TranslateTranslates::TABLE_NAME, $modelRuUpdate);
        $this->assertDatabaseMissing(TranslateTranslates::TABLE_NAME, $modelEsUpdate);
        $this->assertDatabaseMissing(TranslateTranslates::TABLE_NAME, $modelUkUpdate);

        $this->putJson(
            route('v1.saas.translates.update', $translate->id),
            $postData
        )
            ->assertOk();

        $this->assertDatabaseHas(TranslateTranslates::TABLE_NAME, $modelEuUpdate);
        $this->assertDatabaseHas(TranslateTranslates::TABLE_NAME, $modelRuUpdate);
        $this->assertDatabaseHas(TranslateTranslates::TABLE_NAME, $modelEsUpdate);
        $this->assertDatabaseHas(TranslateTranslates::TABLE_NAME, $modelUkUpdate);
    }
}
