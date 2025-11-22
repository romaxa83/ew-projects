<?php

namespace Tests\Feature\Mutations\BackOffice\Localization;

use App\GraphQL\Mutations\BackOffice\Localization\DeleteTranslateMutation;
use App\Models\Admins\Admin;
use App\Models\Localization\Translate;
use App\Permissions\Localization\TranslateDeletePermission;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use JetBrains\PhpStorm\ArrayShape;
use Tests\TestCase;
use Tests\Traits\Permissions\RoleHelperHelperTrait;

class DeleteTranslateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use RoleHelperHelperTrait;

    public const MUTATION = DeleteTranslateMutation::NAME;

    public function test_user_cant_delete_translates(): void
    {
        $this->loginAsUser();

        $this->test_not_admin_cant_delete_translate();
    }

    public function test_not_admin_cant_delete_translate(): void
    {
        $attributes = $this->translateAttributes();
        Translate::factory()->create($attributes);

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
            'lang' => 'uk',
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
        Translate::factory()->create($attributes);

        $otherTranslate = [
            'place' => $attributes['place'],
            'key' => 'some.other.key',
            'text' => 'Some other translate string',
            'lang' => $attributes['lang'],
        ];
        Translate::factory()->create($otherTranslate);

        $query = sprintf(
            'mutation { %s ( place: "%s" key: "%s" lang: "%s")}',
            self::MUTATION,
            $attributes['place'],
            $attributes['key'],
            $attributes['lang']
        );

        $result = $this->postGraphQLBackOffice(['query' => $query]);
        $result->assertJsonPath('data.' . self::MUTATION, true);

        $this->assertDatabaseMissing(Translate::TABLE, $attributes);
        $this->assertDatabaseHas(Translate::TABLE, $otherTranslate);
    }

    protected function loginAsAdminWithCorrectPermission(): Admin
    {
        return $this->loginAsAdmin()
            ->assignRole(
                $this->generateRole('Translator', [TranslateDeletePermission::KEY], Admin::GUARD)
            );
    }
}
