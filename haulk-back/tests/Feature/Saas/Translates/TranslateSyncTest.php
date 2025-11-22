<?php

namespace Tests\Feature\Saas\Translates;

use App\Models\Translates\Translate;
use App\Models\Translates\TranslateTranslates;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class TranslateSyncTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function it_has_unauthenticated_status_when_try_to_sync_translates()
    {
        $this->postJson(
            route('v1.saas.translates.sync'),
            [
                'translates' => $this->getTranslates(),
            ]
        )
            ->assertUnauthorized();
    }

    protected function getTranslates(): array
    {
        return [
            [
                'key' => 'translate key 1',
                'en' => ['text' => 'translate en 1'],
                'ru' => ['text' => 'translate ru 1'],
                'es' => ['text' => 'translate es 1'],
                'uk' => ['text' => 'translate uk 1'],
            ],
            [
                'key' => 'translate key 2',
                'en' => ['text' => 'translate en 2'],
                'ru' => ['text' => 'translate ru 2'],
                'es' => ['text' => 'translate es 2'],
                'uk' => ['text' => 'translate uk 2'],
            ],
        ];
    }

    /** @test */
    public function it_has_forbidden_status_when_try_to_sync_translates()
    {
        $this->loginAsCarrierDispatcher();

        $this->postJson(
            route('v1.saas.translates.sync'),
            [
                'translates' => $this->getTranslates(),
            ]
        )
            ->assertUnauthorized();
    }

    /** @test */
    public function it_sync_translates_success_as_super_admin()
    {
        $this->loginAsSaasSuperAdmin();

        $this->withoutExceptionHandling();

        $this->assertDatabaseMissing(
            Translate::TABLE_NAME,
            [
                'key' => 'translate key 1',
            ]
        );

        $this->assertDatabaseMissing(
            Translate::TABLE_NAME,
            [
                'key' => 'translate key 2',
            ]
        );

        $this->postJson(
            route('v1.saas.translates.sync'),
            [
                'translates' => $this->getTranslates(),
            ]
        )
            ->assertOk();

        $this->assertDatabaseHas(
            Translate::TABLE_NAME,
            [
                'key' => 'translate key 1',
            ]
        );

        $this->assertDatabaseHas(
            TranslateTranslates::TABLE_NAME,
            [
                'text' => 'translate en 1',
                'language' => 'en',
            ]
        );

        $this->assertDatabaseHas(
            Translate::TABLE_NAME,
            [
                'key' => 'translate key 2',
            ]
        );

        $this->assertDatabaseHas(
            TranslateTranslates::TABLE_NAME,
            [
                'text' => 'translate en 2',
                'language' => 'en',
            ]
        );

    }
}
