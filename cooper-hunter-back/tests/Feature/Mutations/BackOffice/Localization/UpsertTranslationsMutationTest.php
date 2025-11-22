<?php

namespace Tests\Feature\Mutations\BackOffice\Localization;

use App\GraphQL\Mutations\BackOffice\Localization\UpsertTranslationsMutation;
use App\Models\Localization\Translate;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class UpsertTranslationsMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = UpsertTranslationsMutation::NAME;

    public function test_upsert_translations(): void
    {
        $this->loginAsSuperAdmin();

        $json = file_get_contents(storage_path('testing/translations.json'));

        Translate::factory()
            ->state(
                [
                    'place' => Translate::APP_PLACE,
                    'key' => 'app_key',
                    'text' => 'app key en modified',
                    'lang' => 'en',
                ]
            )
            ->create();

        $this->assertDatabaseMissing(
            Translate::class,
            [
                'place' => Translate::ADMIN_PLACE,
                'key' => 'admin_key',
                'text' => 'admin key en',
                'lang' => 'en',
            ]
        );

        $this->assertDatabaseMissing(
            Translate::class,
            [
                'place' => Translate::ADMIN_PLACE,
                'key' => 'admin_key',
                'text' => 'admin key es',
                'lang' => 'es',
            ]
        );

        self::assertEquals(
            'site_key',
            _t(
                Translate::SITE_PLACE,
                'site_key'
            )
        );

        $this->postGraphQlBackOfficeUpload(
            GraphQLQuery::upload(self::MUTATION)
                ->args(
                    [
                        'json' => UploadedFile::fake()->createWithContent('translations.json', $json)
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        self::MUTATION => true
                    ],
                ]
            );

        $this->assertDatabaseHas(
            Translate::class,
            [
                'place' => Translate::ADMIN_PLACE,
                'key' => 'admin_key',
                'text' => 'admin key en',
                'lang' => 'en',
            ]
        );

        $this->assertDatabaseHas(
            Translate::class,
            [
                'place' => Translate::ADMIN_PLACE,
                'key' => 'admin_key',
                'text' => 'admin key es',
                'lang' => 'es',
            ]
        );

        $this->assertDatabaseMissing(
            Translate::class,
            [
                'place' => Translate::APP_PLACE,
                'key' => 'app_key',
                'text' => 'app key en',
                'lang' => 'en',
            ]
        );

        $this->assertDatabaseHas(
            Translate::class,
            [
                'place' => Translate::APP_PLACE,
                'key' => 'app_key',
                'text' => 'app key en modified',
                'lang' => 'en',
            ]
        );

        self::assertEquals(
            'site key en',
            _t(
                Translate::SITE_PLACE,
                'site_key'
            )
        );
    }
}