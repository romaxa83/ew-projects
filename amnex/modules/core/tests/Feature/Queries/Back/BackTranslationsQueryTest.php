<?php

declare(strict_types=1);

namespace Wezom\Core\Tests\Feature\Queries\Back;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\TestResponse;
use JsonException;
use Wezom\Admins\Testing\TestCase;
use Wezom\Core\Enums\OrderDirectionEnum;
use Wezom\Core\Enums\TranslationOrderColumnEnum;
use Wezom\Core\Enums\TranslationSideEnum;
use Wezom\Core\GraphQL\Queries\Back\BackTranslations;
use Wezom\Core\Models\Translation;
use Wezom\Core\Testing\QueryBuilder\GraphQLQuery;

class BackTranslationsQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = BackTranslations::NAME;

    public function testCantGetListOfAdminsForSimpleUser(): void
    {
        $this->loginAsAdmin();

        Translation::factory()->times(3)->create();

        $result = $this->queryRequest();

        $this->assertGraphQlForbidden($result);
    }

    public function testCantGetListOfAdminsForNotPermittedUser(): void
    {
        Translation::factory()->times(3)->create();

        $result = $this->queryRequest();

        $this->assertGraphQlUnauthorized($result);
    }

    /**
     * @throws JsonException
     */
    public function testItGetAdminListForPermittedAdmin(): void
    {
        $this->loginAsAdminWithPermissions(['translations.view']);

        Translation::factory()->times(9)->create();

        $result = $this->queryRequest()
            ->assertNoErrors();

        $translations = $result->json('data.' . self::QUERY);

        self::assertCount(9, $translations);
    }

    public function testCanFilterSide(): void
    {
        $this->loginAsAdminWithPermissions(['translations.view']);

        $side = TranslationSideEnum::COMMON();
        Translation::factory()->times(5)->create(['side' => $side]);
        Translation::factory()->times(50)->create(['side' => TranslationSideEnum::ADMIN]);

        $result = $this->queryRequest(['side' => $side])
            ->assertNoErrors();

        $translations = $result->json('data.' . self::QUERY);
        self::assertCount(5, $translations);
    }

    public function testCanFilterMultipleSide(): void
    {
        $this->loginAsAdminWithPermissions(['translations.view']);

        Translation::factory()->times(5)->create(['side' => TranslationSideEnum::COMMON]);
        Translation::factory()->times(30)->create(['side' => TranslationSideEnum::ADMIN]);
        Translation::factory()->times(10)->create(['side' => TranslationSideEnum::SITE]);

        $result = $this->queryRequest(['side' => [TranslationSideEnum::COMMON(), TranslationSideEnum::SITE()]])
            ->assertNoErrors();

        $translations = $result->json('data.' . self::QUERY);
        self::assertCount(15, $translations);
    }

    public function testCanFilterKey(): void
    {
        $this->loginAsAdminWithPermissions(['translations.view']);
        $key = 'validation_admin.first_name';
        $key2 = 'validation_admin.phone';
        Translation::factory()->create(['key' => $key]);
        Translation::factory()->create(['key' => $key2]);
        Translation::factory()->times(5)->create();

        $result = $this->queryRequest(['key' => $key])
            ->assertNoErrors();

        $translations = $result->json('data.' . self::QUERY);
        self::assertCount(1, $translations);

        $translation = array_shift($translations);
        self::assertEquals($key, $translation['key']);

        $result = $this->queryRequest(['key' => $key2])
            ->assertNoErrors();

        $translations = $result->json('data.' . self::QUERY);
        self::assertCount(1, $translations);

        $translation = array_shift($translations);
        self::assertEquals($key2, $translation['key']);

        $result = $this->queryRequest(['key' => 'validation_admin'])
            ->assertNoErrors();

        $translations = $result->json('data.' . self::QUERY);
        self::assertCount(2, $translations);

        $result = $this->queryRequest()
            ->assertNoErrors();

        $translations = $result->json('data.' . self::QUERY);
        self::assertCount(7, $translations);
    }

    public function testCanFilterLanguage(): void
    {
        $this->loginAsAdminWithPermissions(['translations.view']);

        $language = config('translations.admin.default');
        Translation::factory()->times(5)->create(['language' => $language]);

        $result = $this->queryRequest(['language' => $language])
            ->assertNoErrors();

        $translations = $result->json('data.' . self::QUERY);
        self::assertCount(5, $translations);
    }

    protected function queryRequest(array $args = []): TestResponse
    {
        return $this->postGraphQL(GraphQLQuery::query(self::QUERY)
            ->args($args)
            ->select([
                'id',
                'language',
                'text',
                'key',
                'side',
                'createdAt',
                'updatedAt',
            ])
            ->make());
    }

    public function testSortById(): void
    {
        $this->loginAsSuperAdmin();

        $model1 = Translation::factory()->create();
        $model2 = Translation::factory()->create();
        $model3 = Translation::factory()->create();
        $model4 = Translation::factory()->create();

        $model2->touch();

        $ids = $this->query(self::QUERY)
            ->select('id')
            ->ordering(TranslationOrderColumnEnum::ID, OrderDirectionEnum::DESC)
            ->execute()
            ->pluck('id')
            ->values();

        $this->assertCount(4, $ids);

        $this->assertTrue($ids->get(0) == $model4->id);
        $this->assertTrue($ids->get(1) == $model3->id);
        $this->assertTrue($ids->get(2) == $model2->id);
        $this->assertTrue($ids->get(3) == $model1->id);
    }

    public function testSortBySide(): void
    {
        $this->loginAsSuperAdmin();

        $model1 = Translation::factory()->create(['side' => TranslationSideEnum::ADMIN]);
        $model2 = Translation::factory()->create(['side' => TranslationSideEnum::SITE]);

        $ids = $this->query(self::QUERY)
            ->select('id')
            ->ordering(TranslationOrderColumnEnum::SIDE, OrderDirectionEnum::DESC)
            ->execute()
            ->pluck('id')
            ->values();

        $this->assertCount(2, $ids);

        $this->assertTrue($ids->get(0) == $model2->id);
        $this->assertTrue($ids->get(1) == $model1->id);
    }

    public function testSortByLanguage(): void
    {
        $this->loginAsSuperAdmin();

        $model1 = Translation::factory()->create(['language' => 'ru']);
        $model2 = Translation::factory()->create(['language' => 'uk']);

        $ids = $this->query(self::QUERY)
            ->select('id')
            ->ordering(TranslationOrderColumnEnum::LANGUAGE, OrderDirectionEnum::DESC)
            ->execute()
            ->pluck('id')
            ->values();

        $this->assertCount(2, $ids);

        $this->assertTrue($ids->get(0) == $model2->id);
        $this->assertTrue($ids->get(1) == $model1->id);
    }

    public function testSortByCreatedAt(): void
    {
        $this->loginAsSuperAdmin();

        $model1 = Translation::factory()->create(['created_at' => now()->subHour()]);
        $model2 = Translation::factory()->create(['created_at' => now()]);
        $model3 = Translation::factory()->create(['created_at' => now()->subHours(3)]);
        $model4 = Translation::factory()->create(['created_at' => now()->addHours(2)]);

        $ids = $this->query(self::QUERY)
            ->select('id')
            ->ordering(TranslationOrderColumnEnum::CREATED_AT, OrderDirectionEnum::DESC)
            ->execute()
            ->pluck('id')
            ->values();

        $this->assertCount(4, $ids);

        $this->assertTrue($ids->get(0) == $model4->id);
        $this->assertTrue($ids->get(1) == $model2->id);
        $this->assertTrue($ids->get(2) == $model1->id);
        $this->assertTrue($ids->get(3) == $model3->id);
    }

    public function testSortByUpdatedAt(): void
    {
        $this->loginAsSuperAdmin();

        $model1 = Translation::factory()->create(['updated_at' => now()->subHour()]);
        $model2 = Translation::factory()->create(['updated_at' => now()]);
        $model3 = Translation::factory()->create(['updated_at' => now()->subHours(3)]);
        $model4 = Translation::factory()->create(['updated_at' => now()->addHours(2)]);

        $ids = $this->query(self::QUERY)
            ->select('id')
            ->ordering(TranslationOrderColumnEnum::UPDATED_AT, OrderDirectionEnum::DESC)
            ->execute()
            ->pluck('id')
            ->values();

        $this->assertCount(4, $ids);

        $this->assertTrue($ids->get(0) == $model4->id);
        $this->assertTrue($ids->get(1) == $model2->id);
        $this->assertTrue($ids->get(2) == $model1->id);
        $this->assertTrue($ids->get(3) == $model3->id);
    }
}
