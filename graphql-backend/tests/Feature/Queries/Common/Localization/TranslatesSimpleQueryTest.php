<?php

namespace Tests\Feature\Queries\Common\Localization;

use App\GraphQL\Queries\Common\Localization\TranslatesSimpleQuery;
use App\Models\Localization\Translate;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class TranslatesSimpleQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = TranslatesSimpleQuery::NAME;

    public function test_it_get_translates_for_single_place_and_lang(): void
    {
        $place = 'site';
        $lang = 'uk';
        Translate::factory()->times(21)->create(
            [
                'place' => $place,
                'lang' => $lang,
            ]
        );

        Translate::factory()->times(30)->create(
            [
                'place' => 'admin',
                'lang' => $lang,
            ]
        );

        $query = sprintf(
            'query {
                      %s (place: ["%s"] lang: ["%s"]) {
                          place
                          key
                          text
                          lang
                      }
                    }',
            self::QUERY,
            $place,
            $lang
        );

        $response = $this->postGraphQL(['query' => $query]);

        self::assertCount(21, $response->json('data.' . self::QUERY));
    }

    public function test_it_get_translates_for_multiple_places_and_langs(): void
    {
        foreach (['site', 'admin'] as $place) {
            foreach (['uk', 'ru', 'en'] as $lang) {
                Translate::factory()->times(5)->create(
                    [
                        'place' => $place,
                        'lang' => $lang,
                    ]
                );
            }
        }

        $response = $this->query(['site', 'admin'], ['ru']);
        self::assertCount(10, $response->json('data.' . self::QUERY));

        $response = $this->query(['site'], ['ru', 'en']);
        self::assertCount(10, $response->json('data.' . self::QUERY));

        $response = $this->query(['site', 'admin'], ['uk', 'en']);
        self::assertCount(20, $response->json('data.' . self::QUERY));

        $response = $this->query(['admin'], ['uk', 'ru', 'en']);
        self::assertCount(15, $response->json('data.' . self::QUERY));
    }

    protected function query(array $places, array $langs): TestResponse
    {
        $query = sprintf(
            'query {
                    %s (
                        place: ["%s"]
                        lang: ["%s"]
                    ) {
                        place
                        key
                        text
                        lang
                      }
                    }',
            self::QUERY,
            implode('", "', $places),
            implode('", "', $langs)
        );

        return $this->postGraphQL(['query' => $query]);
    }
}
