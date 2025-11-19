<?php

declare(strict_types=1);

namespace Wezom\Core\Tests\Feature\Mutations\Back;

use Illuminate\Testing\TestResponse;
use Wezom\Admins\Testing\TestCase;
use Wezom\Core\Enums\TranslationSideEnum;
use Wezom\Core\Models\Translation;
use Wezom\Core\Testing\QueryBuilder\GraphQLQuery;

class BackTranslationMassCreateTest extends TestCase
{
    protected function attrs(): array
    {
        return [
            'translations' => languages()->map(fn ($model, $locale) => [
                'key' => 'validation.first_name',
                'language' => $locale,
                'text' => 'first_name-' . $locale,
                'side' => TranslationSideEnum::ADMIN(),
            ])->values()->all(),
        ];
    }

    public function testFailsOnGuest(): void
    {
        $attrs = $this->attrs();
        $result = $this->createRequest($attrs);

        $this->assertGraphQlUnauthorized($result);
    }

    public function testFailsWithoutPermission(): void
    {
        $this->loginAsAdmin();

        $attrs = $this->attrs();
        $result = $this->createRequest($attrs);

        $this->assertGraphQlForbidden($result);
    }

    public function testDoSuccess(): void
    {
        $this->loginAsAdminWithPermissions(['translations.create']);
        $attrs = $this->attrs();

        $this->assertDatabaseEmpty(Translation::class);

        $result = $this->createRequest($attrs)->assertNoErrors();

        $translations = $result->json('data.' . $this->operationName());

        foreach ($translations as $i => $translation) {
            $this->assertNotNull($translation['id']);
            $this->assertEquals($attrs['translations'][$i]['key'], $translation['key']);
            $this->assertEquals($attrs['translations'][$i]['side'], TranslationSideEnum::fromKey($translation['side']));
            $this->assertEquals($attrs['translations'][$i]['text'], $translation['text']);
            $this->assertEquals($attrs['translations'][$i]['language'], $translation['language']);
        }

        foreach ($attrs['translations'] as $array) {
            $this->assertDatabaseHas(
                Translation::class,
                [
                    'key' => $array['key'],
                    'side' => $array['side'],
                    'text' => $array['text'],
                    'language' => $array['language'],
                ]
            );
        }
    }

    public function testFailsOnInvalidLanguage(): void
    {
        $this->loginAsAdminWithPermissions(['translations.create']);
        $attrs = $this->attrs();

        $attrs['translations'][0]['language'] = 'test';

        $this->assertDatabaseEmpty(Translation::class);

        $this->createRequest($attrs)
            ->assertOk()
            ->assertHasValidationMessage('translations.0.language', __('validation.exists'));

        $this->assertDatabaseEmpty(Translation::class);
    }

    public function testIgnoresExistingKeys(): void
    {
        $this->loginAsAdminWithPermissions(['translations.create']);

        $attrs = $this->attrs();

        Translation::insert($attrs['translations']);

        $this->createRequest($attrs)
            ->assertNoErrors()
            ->assertJsonStructure([
                'data' => [$this->operationName()],
            ]);

        foreach ($attrs['translations'] as $array) {
            $this->assertDatabaseHas(
                Translation::class,
                [
                    'key' => $array['key'],
                    'side' => $array['side'],
                    'text' => $array['text'],
                    'language' => $array['language'],
                ]
            );
        }
    }

    protected function createRequest(array $attrs): TestResponse
    {
        return $this->postGraphQL(
            GraphQLQuery::mutation($this->operationName())
                ->args([
                    'translations' => array_map(fn ($array) => [
                        'key' => $array['key'],
                        'language' => $array['language'],
                        'text' => $array['text'],
                        'side' => $array['side'],
                    ], $attrs['translations']),
                ])
                ->select([
                    'id',
                    'key',
                    'language',
                    'text',
                    'side',
                ])
                ->make()
        );
    }
}
