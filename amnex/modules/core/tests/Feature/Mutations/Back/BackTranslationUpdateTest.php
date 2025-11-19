<?php

declare(strict_types=1);

namespace Wezom\Core\Tests\Feature\Mutations\Back;

use Illuminate\Testing\TestResponse;
use JsonException;
use Wezom\Core\Enums\TranslationSideEnum;
use Wezom\Core\Models\Translation;
use Wezom\Core\Testing\QueryBuilder\GraphQLQuery;
use Wezom\Core\Tests\Feature\TranslationTestAbstract;

class BackTranslationUpdateTest extends TranslationTestAbstract
{
    /**
     * @throws JsonException
     */
    public function testCantCrateNewAdminForSimpleUser(): void
    {
        $this->loginAsAdmin();

        $attrs = $this->attrs();
        $translation = Translation::factory()->create();
        $result = $this->updateRequest($translation->id, $attrs);

        $this->assertGraphQlForbidden($result);
    }

    /**
     * @throws JsonException
     */
    public function testCantCreateNewAdminForNotAuthUser(): void
    {
        $attrs = $this->attrs();
        $translation = Translation::factory()->create();
        $result = $this->updateRequest($translation->id, $attrs);

        $this->assertGraphQlUnauthorized($result);
    }

    /**
     * @throws JsonException
     */
    public function testDoSuccess(): void
    {
        $this->loginAsAdminWithPermissions(['translations.update']);
        $attrs = $this->attrs();
        $this->assertDatabaseMissing(
            Translation::class,
            [
                'key' => $attrs['key'],
                'side' => $attrs['side'],
                'text' => $attrs['text'],
                'language' => $attrs['language'],
            ]
        );
        $translation = Translation::factory()->create();

        $result = $this->updateRequest($translation->id, $attrs)->assertNoErrors();
        $translation = $result->json('data.' . $this->operationName());

        $this->assertNotNull($translation['id']);
        $this->assertEquals($attrs['key'], $translation['key']);
        $this->assertEquals($attrs['side'], TranslationSideEnum::fromKey($translation['side']));
        $this->assertEquals($attrs['text'], $translation['text']);
        $this->assertEquals($attrs['language'], $translation['language']);

        $this->assertDatabaseHas(
            Translation::class,
            [
                'key' => $attrs['key'],
                'side' => $attrs['side'],
                'text' => $attrs['text'],
                'language' => $attrs['language'],
            ]
        );
    }

    public function testFailsWithInvalidLanguage(): void
    {
        $this->loginAsAdminWithPermissions(['translations.update']);
        $attrs = $this->attrs();
        $attrs['language'] = 'test';
        $this->assertDatabaseMissing(
            Translation::class,
            [
                'key' => $attrs['key'],
                'side' => $attrs['side'],
                'text' => $attrs['text'],
                'language' => $attrs['language'],
            ]
        );
        $translation = Translation::factory()->create();

        $this->updateRequest($translation->id, $attrs)
            ->assertOk()
            ->assertHasValidationMessage('translation.language', __('core::validation.custom.site_not_valid_locale'));

        $this->assertDatabaseMissing(
            Translation::class,
            [
                'key' => $attrs['key'],
                'side' => $attrs['side'],
                'text' => $attrs['text'],
                'language' => $attrs['language'],
            ]
        );
    }

    public function testDoSuccessNotValidKey(): void
    {
        $this->loginAsAdminWithPermissions(['translations.update']);
        $attrs = $this->attrs();
        Translation::factory()->create($attrs);
        $translation = Translation::factory()->create([]);

        $this->updateRequest($translation->id, $attrs)
            ->assertOk()
            ->assertHasValidationMessage('translation.key', __('validation.unique'));

        $this->assertDatabaseHas(
            Translation::class,
            [
                'key' => $attrs['key'],
                'side' => $attrs['side'],
                'text' => $attrs['text'],
                'language' => $attrs['language'],
            ]
        );
    }

    /**
     * @throws JsonException
     */
    protected function updateRequest(
        int $id,
        array $attrs
    ): TestResponse {
        return $this->postGraphQL(GraphQLQuery::mutation($this->operationName())
            ->args([
                'id' => $id,
                'translation' => [
                    'key' => $attrs['key'],
                    'language' => $attrs['language'],
                    'text' => $attrs['text'],
                    'side' => $attrs['side'],
                ],
            ])->select([
                'id',
                'key',
                'language',
                'text',
                'side',
            ])
            ->make());
    }
}
