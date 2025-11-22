<?php

declare(strict_types=1);

namespace Tests\Feature\Mutations\BackOffice\News;

use App\GraphQL\Mutations\BackOffice\News\NewsUpdateMutation;
use App\Models\News\News;
use App\Models\News\NewsTranslation;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class NewsUpdateMutationTest extends NewsCreateMutationTest
{
    use DatabaseTransactions;

    public const MUTATION = NewsUpdateMutation::NAME;

    public function test_do_success(): void
    {
        $this->loginAsSuperAdmin();

        $news = News::factory()
            ->has(NewsTranslation::factory()->allLocales(), 'translations')
            ->create();

        $newTranslation = [
            'language' => 'en',
            'seo_title' => 'custom seo title en',
            'seo_description' => 'custom seo description en',
            'seo_h1' => 'custom seo h1 en'
        ];

        $data = $this->getData();
        $data['news']['id'] = $news->id;
        $data['news']['translations'][0] = array_merge(
            $data['news']['translations'][0],
            $newTranslation
        );

        $this->assertDatabaseMissing(NewsTranslation::TABLE, $newTranslation);

        $this->mutation($data)
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        self::MUTATION => [
                            'id'
                        ],
                    ]
                ]
            );

        $this->assertDatabaseHas(NewsTranslation::TABLE, $newTranslation);
    }

    public function test_not_permitted_user_get_no_permission_error(): void
    {
        $this->loginAsAdmin();

        $news = News::factory()
            ->has(NewsTranslation::factory()->allLocales(), 'translations')
            ->create();

        $data = $this->getData();
        $data['news']['id'] = $news->id;

        $this->assertServerError($this->mutation($data), 'No permission');
    }

    public function test_guest_get_unauthorized_error(): void
    {
        $news = News::factory()
            ->has(NewsTranslation::factory()->allLocales(), 'translations')
            ->create();

        $data = $this->getData();
        $data['news']['id'] = $news->id;

        $this->assertServerError($this->mutation($data), 'Unauthorized');
    }
}
