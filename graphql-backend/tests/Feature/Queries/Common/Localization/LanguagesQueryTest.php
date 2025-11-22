<?php

namespace Tests\Feature\Queries\Common\Localization;

use App\GraphQL\Queries\Common\Localization\LanguagesQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class LanguagesQueryTest extends TestCase
{
    use DatabaseTransactions;

    public function test_it_get_list_if_languages_for_not_auth_user(): void
    {
        $query = sprintf(
            'query { %s { name, slug } }',
            LanguagesQuery::NAME,
        );
        $result = $this->postGraphQL(['query' => $query])
            ->assertJsonStructure(['data' => [LanguagesQuery::NAME => []]]);

        $languages = $result->json('data.' . LanguagesQuery::NAME);

        self::assertCount(3, $languages);
    }

    public function test_it_get_list_if_languages_for_user(): void
    {
        $this->loginAsUser();

        $this->test_it_get_list_if_languages_for_not_auth_user();
    }

    public function test_it_get_list_if_languages_for_admin(): void
    {
        $this->loginAsAdmin();

        $this->test_it_get_list_if_languages_for_not_auth_user();
    }
}
