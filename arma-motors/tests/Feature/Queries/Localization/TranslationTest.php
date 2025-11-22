<?php

namespace Tests\Feature\Queries\Localization;

use App\Models\Localization\Translation;
use App\Services\Localizations\TranslationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Statuses;

class TranslationTest extends TestCase
{
    use DatabaseTransactions;
    use Statuses;

    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function wrong_without_lang()
    {
        $place = $this->translation_app;
        $data = self::dataMoreTranslate();
        app(TranslationService::class)->createOrUpdate($data);

        $response = $this->graphQL(self::getQueryStrByPlace($place));

        $this->assertArrayHasKey('errors', $response->json());
    }

    /** @test */
    public function get_another_place()
    {
        $data = self::dataMoreTranslate(place: 'app');
        app(TranslationService::class)->createOrUpdate($data);

        $response = $this->graphQL(self::getQueryStrByPlace($this->translation_admin));

        $responseData = $response->json('data.translations');

        $this->assertEmpty($responseData);
    }

    /** @test */
    public function get_success_with_lang()
    {
        $place = $this->translation_app;
        $data = self::dataMoreTranslate();
        app(TranslationService::class)->createOrUpdate($data);
        $response = $this->graphQL(self::getQueryStrByPlaceWithLang($place, [$data['translations'][0]['translation'][0]['lang']]))
            ->assertOk();
        $responseData = $response->json('data.translations');

        $this->assertArrayHasKey('key', $responseData[0]);
        $this->assertArrayHasKey('translation', $responseData[0]);
        $this->assertCount(1, $responseData[0]['translation']);
        $this->assertArrayHasKey('lang', $responseData[0]['translation'][0]);
        $this->assertArrayHasKey('text', $responseData[0]['translation'][0]);

        $this->assertEquals($data['translations'][0]['key'], $responseData[0]['key']);
        $this->assertEquals($data['translations'][0]['translation'][0]['lang'], $responseData[0]['translation'][0]['lang']);
        $this->assertEquals($data['translations'][0]['translation'][0]['text'], $responseData[0]['translation'][0]['text']);
    }

    /** @test */
    public function get_success_with_langs()
    {
        $place = $this->translation_app;
        $data = self::dataMoreTranslate();
        app(TranslationService::class)->createOrUpdate($data);

        $response = $this->graphQL(self::getQueryStrByPlaceWithLangs(
            $place, [
                $data['translations'][0]['translation'][0]['lang'],
                $data['translations'][0]['translation'][1]['lang']
            ]));

        $responseData = $response->json('data.translations');

        $this->assertEquals($data['translations'][0]['key'], $responseData[0]['key']);
        $this->assertEquals($data['translations'][0]['translation'][0]['lang'], $responseData[0]['translation'][0]['lang']);
        $this->assertEquals($data['translations'][0]['translation'][0]['text'], $responseData[0]['translation'][0]['text']);
        $this->assertEquals($data['translations'][0]['translation'][1]['lang'], $responseData[0]['translation'][1]['lang']);
        $this->assertEquals($data['translations'][0]['translation'][1]['text'], $responseData[0]['translation'][1]['text']);
    }

    /** @test */
    public function get_success_with_lang_and_places()
    {
        $translateService = app(TranslationService::class);

        $dataApp = self::dataMoreTranslate();
        $translateService->createOrUpdate($dataApp);

        $dataAdmin = self::dataMoreTranslate(Translation::PLACE_ADMIN, 'admin');
        $translateService->createOrUpdate($dataAdmin);

        $response = $this->graphQL(self::getQueryStrByPlaces([
            $this->translation_app,
            $this->translation_admin
        ]));

        $responseData = $response->json('data.translations');

        $this->assertCount(6, $responseData);
        $this->assertArrayHasKey('place', $responseData[0]);
        $this->assertArrayHasKey('key', $responseData[0]);
        $this->assertArrayHasKey('translation', $responseData[0]);
        $this->assertCount(1 , $responseData[0]['translation']);
        $this->assertArrayHasKey('lang', $responseData[0]['translation'][0]);
        $this->assertArrayHasKey('text', $responseData[0]['translation'][0]);
    }

    /** @test */
    public function get_success_with_langs_and_places()
    {
        $translateService = app(TranslationService::class);

        $dataApp = self::dataMoreTranslate();
        $translateService->createOrUpdate($dataApp);

        $dataAdmin = self::dataMoreTranslate(Translation::PLACE_ADMIN, 'admin');
        $translateService->createOrUpdate($dataAdmin);

        $response = $this->graphQL(self::getQueryStrByPlacesAndLangs([
            $this->translation_app,
            $this->translation_admin
        ], ["ru", "uk"]));

        $responseData = $response->json('data.translations');

        $this->assertCount(6, $responseData);
        $this->assertCount(2 , $responseData[0]['translation']);
    }

    /** @test */
    public function without_params()
    {
        $query = '{
            translations{
                key
               }
            }';

        $response = $this->graphQL($query);

        $this->assertArrayHasKey('errors', $response->json());
    }

    public static function getQueryStrByPlace(string $place): string
    {
        return sprintf('{
            translations(place: [%s]) {
                key
                translation{
                    lang
                    text
                }
               }
            }',
            $place
        );
    }

    public static function getQueryStrByPlaceWithLangs(string $place, array $lang): string
    {
        return '{ translations(place: ['. $place .'], lang: ["'. $lang[0] .'", "'. $lang[1] .'"]) {
            key
            translation{
                lang
                text
            }
        }
        }';
    }

    public static function getQueryStrByPlaces(array $place): string
    {
        return '{ translations(place: ['. $place[0] .', '. $place[1] .'], lang: ["ru"]) {
            place
            key
            translation{
                lang
                text
            }
        }
        }';
    }

    public static function getQueryStrByPlacesAndLangs(array $place, array $lang): string
    {
        return '{ translations(place: ['. $place[0] .', '. $place[1] .'], lang: ["'.$lang[0].'", "'.$lang[1].'"]) {
            place
            key
            translation{
                lang
                text
            }
        }
        }';
    }

    public static function getQueryStrByPlaceWithLang(string $place, array $lang): string
    {
        return '{ translations(place: ['. $place .'], lang: ["'. $lang[0] .'"]) {
            place
            key
            translation{
                lang
                text
            }
        }
        }';
    }

    public static function dataMoreTranslate(
        string $place = Translation::PLACE_APP,
        string $key = 'alias'
    )
    {
        return [
            'place' => $place,
            'translations' => [
                [
                    'key' => $key,
                    'translation' => [
                        [
                            'lang' => 'ru',
                            'text' => $key . '_ru',
                        ],
                        [
                            'lang' => 'uk',
                            'text' => $key . '_uk',
                        ]
                    ]
                ],
                [
                    'key' => $key . '_1',
                    'translation' => [
                        [
                            'lang' => 'ru',
                            'text' => $key . '_ru_1',
                        ],
                        [
                            'lang' => 'uk',
                            'text' => $key . '_uk_1',
                        ]
                    ]
                ],
                [
                    'key' => $key . '_2',
                    'translation' => [
                        [
                            'lang' => 'ru',
                            'text' => $key . '_ru_2',
                        ],
                        [
                            'lang' => 'uk',
                            'text' => $key . '_uk_2',
                        ]
                    ]
                ]
            ]

        ];
    }
}


