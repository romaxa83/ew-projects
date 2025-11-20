<?php

namespace Tests\Feature\Mutations\BackOffice\Localization;

use App\GraphQL\Mutations\BackOffice\Localization\DeleteTranslateMutation;
use App\Models\Admins\Admin;
use App\Models\Localization\Translation;
use App\Permissions\Localization\TranslateDeletePermission;
use JetBrains\PhpStorm\ArrayShape;
use Tests\TestCase;
use Tests\Traits\Permissions\RoleHelperHelperTrait;

class DeleteTranslateMutationTest extends TestCase
{
    use RoleHelperHelperTrait;

    public const MUTATION = DeleteTranslateMutation::NAME;

    public function test_not_admin_cant_delete_translate(): void
    {
        $attributes = $this->translateAttributes();
        Translation::factory()->create($attributes);

        $query = sprintf(
            'mutation { %s ( place: "%s" key: "%s" lang: "%s")}',
            self::MUTATION,
            $attributes['place'],
            $attributes['key'],
            $attributes['lang']
        );

        $result = $this->postGraphQLBackOffice(['query' => $query]);
        $this->assertGraphQlUnauthorized($result);
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

    public function test_cant_delete_translate_for_not_permitted_admin(): void
    {
        $this->loginAsAdmin();

        $this->test_not_admin_cant_delete_translate();
    }

    public function test_admin_delete_translate_success(): void
    {
        $this->loginAsAdminWithCorrectPermission();

        $attributes = $this->translateAttributes();
        Translation::factory()->create($attributes);

        $otherTranslate = [
            'place' => $attributes['place'],
            'key' => 'some.other.key',
            'text' => 'Some other translate string',
            'lang' => $attributes['lang'],
        ];
        Translation::factory()->create($otherTranslate);

        $query = sprintf(
            'mutation { %s ( place: "%s" key: "%s" lang: "%s")}',
            self::MUTATION,
            $attributes['place'],
            $attributes['key'],
            $attributes['lang']
        );

        $result = $this->postGraphQLBackOffice(['query' => $query]);
        $result->assertJsonPath('data.' . self::MUTATION, true);

        $this->assertDatabaseMissing(Translation::TABLE, $attributes);
        $this->assertDatabaseHas(Translation::TABLE, $otherTranslate);
    }

    protected function loginAsAdminWithCorrectPermission(): Admin
    {
        return $this->loginAsAdmin()
            ->assignRole(
                $this->generateRole('Translator', [TranslateDeletePermission::KEY], Admin::GUARD)
            );
    }
}
