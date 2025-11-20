<?php

namespace Tests\Feature\Queries\BackOffice\Localization;

use App\GraphQL\Queries\BackOffice\Localization\TranslatesFilterableQuery;
use App\Models\Admins\Admin;
use App\Models\Localization\Translation;
use App\Permissions\Localization\TranslateListPermission;
use Tests\TestCase;
use Tests\Traits\Permissions\RoleHelperHelperTrait;

class TranslatesFilterableQueryTest extends TestCase
{
    use RoleHelperHelperTrait;

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
        $result = $this->postGraphQLBackOffice(['query' => $query])
            ->assertOk();

        $this->assertGraphQlUnauthorized($result);
    }

    public function test_it_get_some_objects_for_admin(): void
    {
        $this->loginAsAdminWithCorrectPermission();

        Translation::factory()->times(50)->create(['place' => 'site', 'lang' => 'en']);

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
        $response = $this->postGraphQLBackOffice(['query' => $query])
            ->assertJsonStructure(['data' => [self::QUERY => ['data' => []]]]);

        $data = $response->json('data.' . self::QUERY . '.data');
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

        Translation::factory()->times(15)->create(['place' => 'site', 'lang' => 'en']);
        Translation::factory()->times(15)->create(['place' => 'admin', 'lang' => 'en']);
        Translation::factory()->times(15)->create(['place' => 'some-place', 'lang' => 'en']);

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
        $response = $this->postGraphQLBackOffice(['query' => $query])
            ->assertJsonStructure(['data' => [self::QUERY => ['data' => []]]]);

        $data = $response->json('data.' . self::QUERY . '.data');
        self::assertCount(30, $data);
    }

    public function test_it_filter_by_key_for_admin(): void
    {
        $this->loginAsAdminWithCorrectPermission();

        $key = 'some_key';
        $place = 'admin';
        Translation::factory()->times(1)->create(['place' => $place, 'key' => $key, 'lang' => 'en']);
        Translation::factory()->times(25)->create(['place' => $place, 'lang' => 'en']);

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
        $response = $this->postGraphQLBackOffice(['query' => $query])
            ->assertJsonStructure(['data' => [self::QUERY => ['data' => []]]]);

        $data = $response->json('data.' . self::QUERY . '.data');
        self::assertCount(1, $data);
    }

    public function test_it_filter_by_lang_for_admin(): void
    {
        $this->loginAsAdminWithCorrectPermission();

        $place = 'admin';
        Translation::factory()->times(15)->create(['place' => $place, 'lang' => 'en']);

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
            "en"
        );
        $response = $this->postGraphQLBackOffice(['query' => $query]);

        $data = $response->json('data.' . self::QUERY . '.data');
        self::assertCount(15, $data);
    }

    public function test_it_filter_by_search_for_admin(): void
    {
        $this->loginAsAdminWithCorrectPermission();

        $place = 'admin';
        $search = 'term';
        Translation::factory()->create(['place' => $place, 'key' => 'termina']);
        Translation::factory()->create(['place' => $place, 'text' => 'srtermina']);
        Translation::factory()->create(['place' => $place, 'text' => 'srtna']);

        $query = sprintf(
            'query {
                      %s (place: ["%s"] search: "%s") {
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
            $search
        );
        $response = $this->postGraphQLBackOffice(['query' => $query]);

        $data = $response->json('data.' . self::QUERY . '.data');
        self::assertCount(2, $data);
    }

    public function test_it_has_per_page_for_admin(): void
    {
        $this->loginAsAdminWithCorrectPermission();

        $place = 'admin';
        Translation::factory()->times(60)->create(['place' => $place, 'lang' => 'en']);

        $query = sprintf(
            'query {
                      %s (place: ["%s"] per_page: %s) {
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
        $response = $this->postGraphQLBackOffice(['query' => $query]);

        $data = $response->json('data.' . self::QUERY . '.data');
        self::assertCount(20, $data);
    }



    public function test_it_returns_pagination_meta_for_query(): void
    {
        $this->loginAsAdminWithCorrectPermission();

        $total = 51;

        Translation::factory()->times($total)->create(['place' => 'site', 'lang' => 'en']);

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
                            per_page: %s
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
        $response = $this->postGraphQLBackOffice(['query' => $query]);
        $meta = $response->json('data.' . self::QUERY . '.meta');

        self::assertEquals($expectedMeta, $meta);
    }
}
