<?php

declare(strict_types=1);

namespace Wezom\Core\Tests\Feature\Queries\Site;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\TestResponse;
use JsonException;
use Wezom\Admins\Testing\TestCase;
use Wezom\Core\Enums\OrderDirectionEnum;
use Wezom\Core\Enums\TranslationOrderColumnEnum;
use Wezom\Core\Enums\TranslationSideEnum;
use Wezom\Core\GraphQL\Queries\Site\SiteTranslations;
use Wezom\Core\Models\Translation;
use Wezom\Core\Testing\QueryBuilder\GraphQLQuery;

class SiteTranslationsQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = SiteTranslations::NAME;
    public const COUNT = 3;

    protected string $locale;

    protected function setUp(): void
    {
        parent::setUp();

        $this->locale = config('app.locale');
    }

    /**
     * @throws JsonException
     */
    public function testCanGetEmptyList(): void
    {
        Translation::factory()->times(5)->create([
            'side' => TranslationSideEnum::COMMON(),
            'language' => config('translations.admin.default'),
        ]);

        $result = $this->queryRequest([
            'language' => $this->locale,
            'side' => TranslationSideEnum::ADMIN(),
        ]);

        $translations = $result->json('data.' . self::QUERY);
        self::assertCount(0, $translations);
    }

    public function testCanDoSuccess(): void
    {
        $this->loginAsSuperAdmin();

        Translation::factory()->times(5)->create([
            'side' => TranslationSideEnum::COMMON(),
            'language' => config('translations.admin.default'),
        ]);
        Translation::factory()->times(50)->create([
            'side' => TranslationSideEnum::ADMIN(),
            'language' => $this->locale,
        ]);

        $result = $this->queryRequest([
            'language' => $this->locale,
            'side' => TranslationSideEnum::ADMIN(),
        ])
            ->assertNoErrors();

        $translations = $result->json('data.' . self::QUERY);
        self::assertCount(50, $translations);
    }

    public function testGetsMixedTranslations(): void
    {
        $this->loginAsSuperAdmin();

        $this->assertDatabaseEmpty(Translation::class);

        Translation::factory()->times(5)->create([
            'side' => TranslationSideEnum::COMMON(),
            'language' => $this->locale,
        ]);
        Translation::factory()->times(20)->create([
            'side' => TranslationSideEnum::ADMIN(),
            'language' => $this->locale,
        ]);

        $this->assertDatabaseCount(Translation::class, 25);

        $result = $this->queryRequest([
            'language' => $this->locale,
            'side' => [TranslationSideEnum::ADMIN(), TranslationSideEnum::COMMON()],
        ])
            ->assertNoErrors();

        $translations = $result->json('data.' . self::QUERY);
        self::assertCount(25, $translations);
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
        $side = TranslationSideEnum::COMMON();
        $model1 = Translation::factory()->create([
            'side' => $side,
            'language' => $this->locale,
        ]);
        $model2 = Translation::factory()->create([
            'side' => $side,
            'language' => $this->locale,
        ]);
        $model3 = Translation::factory()->create([
            'side' => $side,
            'language' => $this->locale,
        ]);
        $model4 = Translation::factory()->create([
            'side' => $side,
            'language' => $this->locale,
        ]);

        $model2->touch();

        $ids = $this->query(self::QUERY)
            ->select('id')
            ->args([
                'side' => [$side],
                'language' => $this->locale,
            ])
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
        $model1 = Translation::factory()->create([
            'side' => TranslationSideEnum::ADMIN,
            'language' => $this->locale,
        ]);
        $model2 = Translation::factory()->create([
            'side' => TranslationSideEnum::SITE,
            'language' => $this->locale,
        ]);

        $ids = $this->query(self::QUERY)
            ->select('id')
            ->args([
                'side' => [TranslationSideEnum::ADMIN(), TranslationSideEnum::SITE()],
                'language' => $this->locale,
            ])
            ->ordering(TranslationOrderColumnEnum::SIDE, OrderDirectionEnum::DESC)
            ->execute()
            ->pluck('id')
            ->values();

        $this->assertCount(2, $ids);

        $this->assertTrue($ids->get(0) == $model2->id);
        $this->assertTrue($ids->get(1) == $model1->id);
    }

    public function testSortByCreatedAt(): void
    {
        $side = TranslationSideEnum::COMMON();
        $model1 = Translation::factory()->create([
            'created_at' => now()->subHour(),
            'side' => $side,
            'language' => $this->locale,
        ]);
        $model2 = Translation::factory()->create([
            'created_at' => now(),
            'side' => $side,
            'language' => $this->locale,
        ]);
        $model3 = Translation::factory()->create([
            'created_at' => now()->subHours(3),
            'side' => $side,
            'language' => $this->locale,
        ]);
        $model4 = Translation::factory()->create([
            'created_at' => now()->addHours(2),
            'side' => $side,
            'language' => $this->locale,
        ]);

        $ids = $this->query(self::QUERY)
            ->select('id')
            ->args([
                'side' => [$side],
                'language' => $this->locale,
            ])
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
        $side = TranslationSideEnum::COMMON();
        $model1 = Translation::factory()->create([
            'updated_at' => now()->subHour(),
            'side' => $side,
            'language' => $this->locale,
        ]);
        $model2 = Translation::factory()->create([
            'updated_at' => now(),
            'side' => $side,
            'language' => $this->locale,
        ]);
        $model3 = Translation::factory()->create([
            'updated_at' => now()->subHours(3),
            'side' => $side,
            'language' => $this->locale,
        ]);
        $model4 = Translation::factory()->create([
            'updated_at' => now()->addHours(2),
            'side' => $side,
            'language' => $this->locale,
        ]);

        $ids = $this->query(self::QUERY)
            ->select('id')
            ->args([
                'side' => [$side],
                'language' => $this->locale,
            ])
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
