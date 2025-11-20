<?php

namespace Tests\Feature\Queries\Common\Localization;

use App\GraphQL\Queries\Common\Localization\LanguagesQuery;
use App\Models\Localization\Language;
use Tests\TestCase;

class LanguagesQueryTest extends TestCase
{
    public function test_it_get_list_if_languages_for_not_auth_user(): void
    {
        $query = sprintf(
            'query { %s { name, slug } }',
            LanguagesQuery::NAME,
        );
        $result = $this->postGraphQL(['query' => $query])
            ->assertJsonStructure(['data' => [LanguagesQuery::NAME => []]]);

        $languages = $result->json('data.' . LanguagesQuery::NAME);

        $count = Language::count();

        self::assertCount($count, $languages);
    }

    public function test_it_get_list_if_languages_for_admin(): void
    {
        $this->loginAsAdmin();

        $this->test_it_get_list_if_languages_for_not_auth_user();
    }
}
