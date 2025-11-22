<?php

namespace Tests\Feature\Queries\Localization;

use App\Models\Localization\Translation;
use App\Services\Localizations\TranslationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Statuses;

class TranslationHashTest extends TestCase
{
    use DatabaseTransactions;
    use Statuses;

    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function success()
    {
        $place = $this->translation_app;
        $data = $this->dataMoreTranslate();
        app(TranslationService::class)->createOrUpdate($data);

        $response = $this->graphQL(self::getQueryStrByPlace($place))
            ->assertOk();

        $responseData = $response->json('data.translationsHash');

        $this->assertArrayHasKey('status', $responseData);
        $this->assertArrayHasKey('hash', $responseData);
        $this->assertArrayHasKey('message', $responseData);

        $this->assertTrue($responseData['status']);
        $this->assertEmpty($responseData['message']);
        $this->assertNotEmpty($responseData['hash']);
    }

    /** @test */
    public function change_hash()
    {
        $place = $this->translation_app;
        $data = $this->dataMoreTranslate();
        app(TranslationService::class)->createOrUpdate($data);

        $response = $this->graphQL(self::getQueryStrByPlace($place))
            ->assertOk();

        $hash = $response->json('data.translationsHash.hash');

        app(TranslationService::class)->createOrUpdate($this->dataMoreTranslate(key:'test'));

        $response = $this->graphQL(self::getQueryStrByPlace($place))
            ->assertOk();

        $newHash = $response->json('data.translationsHash.hash');

        $this->assertNotEquals($hash, $newHash);
    }

    /** @test */
    public function not_change_hash()
    {
        $place = $this->translation_app;
        $data = $this->dataMoreTranslate();
        app(TranslationService::class)->createOrUpdate($data);

        $response = $this->graphQL(self::getQueryStrByPlace($place))
            ->assertOk();

        $hash = $response->json('data.translationsHash.hash');

        app(TranslationService::class)->createOrUpdate($data);

        $response = $this->graphQL(self::getQueryStrByPlace($place))
            ->assertOk();

        $newHash = $response->json('data.translationsHash.hash');

        $this->assertEquals($hash, $newHash);
    }

    /** @test */
    public function without_params()
    {
        $query = '{
            translationsHash{
                key
               }
            }';

        $response = $this->graphQL($query);

        $this->assertArrayHasKey('errors', $response->json());
    }

    public static function getQueryStrByPlace(string $place): string
    {
        return sprintf('{
            translationsHash(place: %s) {
                status
                hash
                message
               }
            }',
            $place
        );
    }

    private function dataMoreTranslate(
        string $place = Translation::PLACE_APP,
        string $key = 'alias',
        string $ru_text = 'alias_ru',
        string $uk_text  = 'alias_uk'
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
                            'text' => $ru_text,
                        ],
                        [
                            'lang' => 'uk',
                            'text' => $uk_text,
                        ]
                    ]
                ],
                [
                    'key' => $key . '_1',
                    'translation' => [
                        [
                            'lang' => 'ru',
                            'text' => $ru_text . '_1',
                        ],
                        [
                            'lang' => 'uk',
                            'text' => $uk_text . '_1',
                        ]
                    ]
                ],
                [
                    'key' => $key . '_2',
                    'translation' => [
                        [
                            'lang' => 'ru',
                            'text' => $ru_text . '_2',
                        ],
                        [
                            'lang' => 'uk',
                            'text' => $uk_text . '_2',
                        ]
                    ]
                ]
            ]

        ];
    }
}



