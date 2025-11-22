<?php

namespace Tests\Feature\Queries\Common\Localization;

use App\GraphQL\Queries\Common\Localization\TranslatesSimpleHashQuery;
use App\Models\Localization\Language;
use App\Models\Localization\Translate;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class TranslatesSimpleHashQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = TranslatesSimpleHashQuery::NAME;

    public function test_it_get_translates_for_single_place_and_lang(): void
    {
        Language::factory()
            ->state(
                [
                    'slug' => 'ru'
                ]
            )
            ->create();

        $place = 'site';
        $lang = 'ru';
        Translate::factory()->times(21)->create(
            [
                'place' => $place,
                'lang' => $lang,
            ]
        );

        $query = sprintf(
            'query {
                      %s (place: ["%s"] lang: ["%s"])
                    }',
            self::QUERY,
            $place,
            $lang
        );

        $hash1 = $this->postGraphQL(compact('query'))->json('data.' . self::QUERY);
        $hash1_1 = $this->postGraphQL(compact('query'))->json('data.' . self::QUERY);

        self::assertEquals($hash1, $hash1_1);

        Translate::factory()->create(
            [
                'place' => $place,
                'lang' => $lang,
            ]
        );

        Carbon::setTestNow(now()->addMinutes(2));

        $hash2 = $this->postGraphQL(compact('query'))->json('data.' . self::QUERY);
        $hash2_2 = $this->postGraphQL(compact('query'))->json('data.' . self::QUERY);

        self::assertEquals($hash2, $hash2_2);

        self::assertNotEquals($hash1, $hash2);
    }

    protected function query(array $places, array $langs): TestResponse
    {
        $query = sprintf(
            'query {
                    %s (
                        place: ["%s"]
                        lang: ["%s"]
                    )
                    }',
            self::QUERY,
            implode('", "', $places),
            implode('", "', $langs)
        );

        return $this->postGraphQL(compact('query'));
    }
}
