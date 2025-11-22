<?php

namespace Tests\Feature\Queries\BackOffice\Localization;

use App\GraphQL\Queries\BackOffice\Localization\TranslatesFilterableQuery;
use App\Models\Admins\Admin;
use App\Models\Localization\Language;
use App\Models\Localization\Translate;
use App\Permissions\Localization\TranslateListPermission;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Permissions\RoleHelperTrait;

class TranslatesFilterableQueryTest extends TestCase
{
    use DatabaseTransactions;
    use RoleHelperTrait;

    public const QUERY = TranslatesFilterableQuery::NAME;

    public function test_cant_get_list_of_translates_for_not_permitted_admin(): void
    {
        $this->loginAsAdmin();

        $this->test_it_not_get_filterable_translates_for_not_auth_users();
    }

    public function test_it_not_get_filterable_translates_for_not_auth_users(): void
    {
        $query = sprintf(
            'query {
                      %s (place: ["%s"]) {
                        data {
                          place
                          key
                          text
                          lang
                        }
                      }
                    }
                    ',
            self::QUERY,
            'site'
        );
        $result = $this->postGraphQLBackOffice(compact('query'))
            ->assertOk();

        $this->assertGraphQlUnauthorized($result);
    }

    public function test_it_get_some_objects_for_admin(): void
    {
        $this->loginAsAdminWithCorrectPermission();

        Translate::factory()->times(50)->create(['place' => 'site', 'lang' => 'en']);

        $query = sprintf(
            'query {
                      %s (place: ["%s"]) {
                        data {
                          place
                          key
                          text
                          lang
                        }
                      }
                    }
                    ',
            self::QUERY,
            'site'
        );
        $response = $this->postGraphQLBackOffice(compact('query'))
            ->assertJsonStructure(['data' => [self::QUERY => ['data' => []]]]);

        $data = $response->json('data.'.self::QUERY.'.data');
        self::assertCount(50, $data);
    }

    protected function loginAsAdminWithCorrectPermission(): Admin
    {
        return $this->loginAsAdmin()
            ->assignRole(
                $this->generateRole('Translator', [TranslateListPermission::KEY], Admin::GUARD)
            );
    }

    public function test_it_filter_by_place_for_admin(): void
    {
        $this->loginAsAdminWithCorrectPermission();

        Translate::factory()->times(15)->create(['place' => 'site', 'lang' => 'en']);
        Translate::factory()->times(15)->create(['place' => 'admin', 'lang' => 'en']);
        Translate::factory()->times(15)->create(['place' => 'some-place', 'lang' => 'en']);

        $query = sprintf(
            'query {
                      %s (place: ["%s"]) {
                        data {
                          place
                          key
                          text
                          lang
                        }
                      }
                    }
                    ',
            self::QUERY,
            'site", "admin'
        );
        $response = $this->postGraphQLBackOffice(compact('query'))
            ->assertJsonStructure(['data' => [self::QUERY => ['data' => []]]]);

        $data = $response->json('data.'.self::QUERY.'.data');
        self::assertCount(30, $data);
    }

    public function test_it_filter_by_key_for_admin(): void
    {
        $this->loginAsAdminWithCorrectPermission();

        $key = 'some_key';
        $place = 'admin';
        Translate::factory()->times(1)->create(['place' => $place, 'key' => $key, 'lang' => 'en']);
        Translate::factory()->times(25)->create(['place' => $place, 'lang' => 'en']);

        $query = sprintf(
            'query {
                      %s (place: ["%s"] key: "%s") {
                        data {
                          place
                          key
                          text
                          lang
                        }
                      }
                    }
                    ',
            self::QUERY,
            $place,
            $key
        );
        $response = $this->postGraphQLBackOffice(compact('query'))
            ->assertJsonStructure(['data' => [self::QUERY => ['data' => []]]]);

        $data = $response->json('data.'.self::QUERY.'.data');
        self::assertCount(1, $data);
    }

    public function test_it_filter_by_lang_for_admin(): void
    {
        $this->loginAsAdminWithCorrectPermission();

        $place = 'admin';

        Language::factory()
            ->state(
                [
                    'slug' => 'ru'
                ]
            )
            ->create();

        Translate::factory()->times(15)->create(['place' => $place, 'lang' => 'en']);
        Translate::factory()->times(15)->create(['place' => $place, 'lang' => 'ru']);
        Translate::factory()->times(15)->create(['place' => $place, 'lang' => 'en']);

        $query = sprintf(
            'query {
                      %s (place: ["%s"] lang: ["%s"]) {
                        data {
                          place
                          key
                          text
                          lang
                        }
                      }
                    }
                    ',
            self::QUERY,
            $place,
            'uk", "en'
        );
        $response = $this->postGraphQLBackOffice(compact('query'));

        $data = $response->json('data.'.self::QUERY.'.data');
        self::assertCount(30, $data);
    }

    public function test_it_has_limit_for_admin(): void
    {
        $this->loginAsAdminWithCorrectPermission();

        $place = 'admin';
        Translate::factory()->times(60)->create(['place' => $place, 'lang' => 'en']);

        $query = sprintf(
            'query {
                      %s (place: ["%s"] limit: %s) {
                        data {
                          place
                          key
                          text
                          lang
                        }
                      }
                    }
                    ',
            self::QUERY,
            $place,
            20
        );
        $response = $this->postGraphQLBackOffice(compact('query'));

        $data = $response->json('data.'.self::QUERY.'.data');
        self::assertCount(20, $data);
    }

    public function test_it_get_correct_sorting_for_admin(): void
    {
        $this->loginAsAdminWithCorrectPermission();

        $place = 'admin';
        $text1 = 'Some text 1';
        Translate::factory()->create(
            [
                'place' => $place,
                'text' => $text1,
                'lang' => 'en'
            ]
        );
        $text2 = 'Some text 2';
        Translate::factory()->create(
            [
                'place' => $place,
                'text' => $text2,
                'lang' => 'en'
            ]
        );

        $query = sprintf(
            'query {
                      %s (place: ["%s"] sort: "%s") {
                        data {
                          place
                          key
                          text
                          lang
                        }
                      }
                    }
                    ',
            self::QUERY,
            $place,
            'text-asc'
        );
        $response = $this->postGraphQLBackOffice(compact('query'));

        $data = $response->json('data.'.self::QUERY.'.data');

        self::assertEquals($text1, array_shift($data)['text']);
        self::assertEquals($text2, array_shift($data)['text']);

        $query = sprintf(
            'query {
                      %s (place: ["%s"] sort: "%s") {
                        data {
                          place
                          key
                          text
                          lang
                        }
                      }
                    }
                    ',
            self::QUERY,
            $place,
            'text-desc'
        );

        $response = $this->postGraphQLBackOffice(compact('query'));

        $data = $response->json('data.'.self::QUERY.'.data');

        self::assertEquals($text2, array_shift($data)['text']);
        self::assertEquals($text1, array_shift($data)['text']);
    }

    public function test_it_returns_pagination_meta_for_query(): void
    {
        $this->loginAsAdminWithCorrectPermission();

        $total = 51;

        Translate::factory()->times($total)->create(['place' => 'site', 'lang' => 'en']);

        $perPage = 15;
        $page = 3;
        $lastPage = (int)ceil($total / $perPage);
        $expectedMeta = [
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'from' => ($page <= $lastPage) ? $perPage * ($page - 1) + 1 : null,
            'to' => ($page <= $lastPage) ? min($perPage * $page, $total) : null,
            'last_page' => $lastPage,
            'has_more_pages' => $page < $lastPage
        ];

        $query = sprintf(
            'query {
                      %s (
                            place: ["%s"]
                            page: %s
                            limit: %s
                        ) {
                        data {
                          place
                          key
                          text
                          lang
                        }
                        meta {
                          total
                          per_page
                          current_page
                          from
                          to
                          last_page
                          has_more_pages
                        }
                      }
                    }
                    ',
            self::QUERY,
            'site',
            $page,
            $perPage
        );
        $response = $this->postGraphQLBackOffice(compact('query'));
        $meta = $response->json('data.' . self::QUERY . '.meta');

        self::assertEquals($expectedMeta, $meta);
    }
}
