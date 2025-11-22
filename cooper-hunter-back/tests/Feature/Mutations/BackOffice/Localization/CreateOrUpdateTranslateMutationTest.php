<?php

namespace Tests\Feature\Mutations\BackOffice\Localization;

use App\GraphQL\Mutations\BackOffice\Localization\CreateOrUpdateTranslateMutation;
use App\Models\Admins\Admin;
use App\Models\Localization\Translate;
use App\Permissions\Localization\TranslateUpdatePermission;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use JetBrains\PhpStorm\ArrayShape;
use Tests\TestCase;
use Tests\Traits\Permissions\RoleHelperTrait;

class CreateOrUpdateTranslateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use RoleHelperTrait;

    public const MUTATION = CreateOrUpdateTranslateMutation::NAME;

    public function test_it_not_permitted_for_no_admin_user(): void
    {
        $this->loginAsUser();

        $this->test_it_not_permitted_for_not_auth_user();
    }

    public function test_it_not_permitted_for_not_auth_user(): void
    {
        $attributes = $this->translateAttributes();

        $query = sprintf(
            'mutation { %s ( place: "%s" key: "%s" text: "%s" lang: "%s")}',
            self::MUTATION,
            $attributes['place'],
            $attributes['key'],
            $attributes['text'],
            $attributes['lang']
        );

        $this->assertDatabaseMissing(Translate::TABLE, $attributes);

        $result = $this->postGraphQLBackOffice(compact('query'));
        $this->assertGraphQlUnauthorized($result);

        $this->assertDatabaseMissing(Translate::TABLE, $attributes);
    }

    #[ArrayShape(['place' => "string", 'key' => "string", 'text' => "string", 'lang' => "string"])]
    protected function translateAttributes(): array
    {
        return [
            'place' => 'site',
            'key' => 'key.attribute',
            'text' => 'Some translation',
            'lang' => 'en',
        ];
    }

    public function test_cant_create_new_translate_for_not_permitted_admin(): void
    {
        $this->loginAsAdmin();

        $this->test_it_not_permitted_for_not_auth_user();
    }

    public function test_it_create_new_translate_success(): void
    {
        $this->loginAsAdminWithCorrectPermission();

        $attributes = $this->translateAttributes();

        $query = sprintf(
            'mutation { %s ( place: "%s" key: "%s" text: "%s" lang: "%s")}',
            self::MUTATION,
            $attributes['place'],
            $attributes['key'],
            $attributes['text'],
            $attributes['lang']
        );

        $this->assertDatabaseMissing(Translate::TABLE, $attributes);

        $result = $this->postGraphQLBackOffice(compact('query'));

        self::assertTrue($result->json('data.'.self::MUTATION));

        $this->assertDatabaseHas(Translate::TABLE, $attributes);
    }

    protected function loginAsAdminWithCorrectPermission(): Admin
    {
        return $this->loginAsAdmin()
            ->assignRole(
                $this->generateRole('Translator', [TranslateUpdatePermission::KEY], Admin::GUARD)
            );
    }

    public function test_it_update_exists_translate_success(): void
    {
        $this->loginAsAdminWithCorrectPermission();

        $attributes = $this->translateAttributes();

        Translate::factory()->create($attributes);

        $newAttributes = $attributes;
        $newAttributes['text'] = 'Some other not exists translation text';

        $query = sprintf(
            'mutation { %s ( place: "%s" key: "%s" text: "%s" lang: "%s")}',
            self::MUTATION,
            $newAttributes['place'],
            $newAttributes['key'],
            $newAttributes['text'],
            $newAttributes['lang']
        );

        $this->assertDatabaseHas(Translate::TABLE, $attributes);
        $this->assertDatabaseMissing(Translate::TABLE, $newAttributes);

        $result = $this->postGraphQLBackOffice(compact('query'));

        self::assertTrue($result->json('data.'.self::MUTATION));

        $this->assertDatabaseMissing(Translate::TABLE, $attributes);
        $this->assertDatabaseHas(Translate::TABLE, $newAttributes);
    }

    public function test_it_has_validation_message_when_lang_is_not_exists_language_slug(): void
    {
        $this->loginAsAdminWithCorrectPermission();

        $attributes = $this->translateAttributes();

        Translate::factory()->create($attributes);

        $newAttributes = $attributes;
        $newAttributes['text'] = 'Some other not exists translation text';

        $query = sprintf(
            'mutation { %s ( place: "%s" key: "%s" text: "%s" lang: "%s")}',
            self::MUTATION,
            $newAttributes['place'],
            $newAttributes['key'],
            $newAttributes['text'],
            'ukr'
        );

        $result = $this->postGraphQLBackOffice(compact('query'));

        $this->assertResponseHasValidationMessage(
            $result,
            'lang',
            [__('validation.custom.lang.exist-languages', ['attribute' => 'lang'])]
        );
    }

}
