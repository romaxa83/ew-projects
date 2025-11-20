<?php

namespace Tests\Feature\Queries\Common\Localization;

use App\GraphQL\Queries\Common\Localization\TranslatesSimpleQuery;
use App\Models\Localization\Translation;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class TranslatesSimpleQueryTest extends TestCase
{
    public const QUERY = TranslatesSimpleQuery::NAME;

    public function test_it_get_translates_for_single_place_and_lang(): void
    {
        $place = 'site';
        $lang = 'en';

        Translation::factory()->times(21)->create(
            [
                'place' => $place,
                'lang' => $lang,
            ]
        );

        Translation::factory()->times(30)->create(
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
            foreach (['en'] as $lang) {
                Translation::factory()->times(5)->create(
                    [
                        'place' => $place,
                        'lang' => $lang,
                    ]
                );
            }
        }

        $response = $this->query(['site'], ['es', 'en']);
        self::assertCount(5, $response->json('data.' . self::QUERY));
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
