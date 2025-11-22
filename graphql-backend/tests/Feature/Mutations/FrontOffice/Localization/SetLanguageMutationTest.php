<?php

namespace Tests\Feature\Mutations\FrontOffice\Localization;

use App\GraphQL\Mutations\Common\Localization\SetLanguageMutation;
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
            'ru'
        );

        $result = $this->postGraphQL(['query' => $query]);
        $this->assertGraphQlUnauthorized($result);
    }

    public function test_user_can_set_lang(): void
    {
        $user = $this->loginAsUser();
        $newLangSlug = 'ru';
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

        $result = $this->postGraphQL(['query' => $query]);

        self::assertTrue($result->json('data.' . self::MUTATION));

        $this->assertDatabaseHas(
            User::TABLE,
            [
                'id' => $user->id,
                'lang' => $newLangSlug,
            ]
        );
    }

}
