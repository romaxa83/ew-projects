<?php

namespace Tests\Feature\Mutations\FrontOffice\Localization;

use App\GraphQL\Mutations\Common\Localization\SetLanguageMutation;
use App\Models\Technicians\Technician;
use App\Models\Users\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class SetLanguageMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = SetLanguageMutation::NAME;

    public function test_cant_set_lang_for_not_auth_user(): void
    {
        $query = sprintf(
            'mutation { %s (lang: "%s") }',
            self::MUTATION,
            'en'
        );

        $result = $this->postGraphQL(compact('query'));
        $this->assertGraphQlUnauthorized($result);
    }

    public function test_user_can_set_lang(): void
    {
        $user = $this->loginAsUser();
        $newLangSlug = 'en';
        $query = sprintf(
            'mutation { %s (lang: "%s") }',
            self::MUTATION,
            $newLangSlug
        );

        $this->assertDatabaseHas(
            User::TABLE,
            [
                'id' => $user->id,
                'lang' => $user->lang,
            ]
        );

        $result = $this->postGraphQL(compact('query'));

        self::assertTrue($result->json('data.'.self::MUTATION));

        $this->assertDatabaseHas(
            User::TABLE,
            [
                'id' => $user->id,
                'lang' => $newLangSlug,
            ]
        );
    }

    public function test_technician_can_set_language(): void
    {
        $user = $this->loginAsTechnician();
        $newLangSlug = 'es';
        $query = sprintf(
            'mutation { %s (lang: "%s") }',
            self::MUTATION,
            $newLangSlug
        );

        $this->assertDatabaseHas(
            Technician::TABLE,
            [
                'id' => $user->id,
                'lang' => $oldLang = $user->lang,
            ]
        );

        $result = $this->postGraphQL(compact('query'));

        self::assertTrue($result->json('data.'.self::MUTATION));

        $this->assertDatabaseHas(
            Technician::TABLE,
            [
                'id' => $user->id,
                'lang' => $newLangSlug,
            ]
        );

        $this->assertDatabaseMissing(
            Technician::TABLE,
            [
                'id' => $user->id,
                'lang' => $oldLang,
            ]
        );
    }
}
