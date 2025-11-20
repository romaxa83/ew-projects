<?php

namespace Tests\Feature\Mutations\BackOffice\Localization;

use App\GraphQL\Mutations\Common\Localization\SetLanguageMutation;
use App\Models\Admins\Admin;
use Tests\TestCase;

class SetLanguageMutationTest extends TestCase
{
    public const MUTATION = SetLanguageMutation::NAME;

    public function test_admin_can_set_lang(): void
    {
        $admin = $this->loginAsAdmin();
        $newLangSlug = 'en';
        $query = sprintf(
            'mutation { %s (lang: "%s") }',
            self::MUTATION,
            $newLangSlug
        );

        $this->assertDatabaseHas(
            Admin::TABLE,
            [
                'id' => $admin->id,
                'lang' => $admin->lang,
            ]
        );

        $result = $this->postGraphQLBackOffice(['query' => $query]);

        self::assertTrue($result->json('data.' . self::MUTATION));

        $this->assertDatabaseHas(
            Admin::TABLE,
            [
                'id' => $admin->id,
                'lang' => $newLangSlug,
            ]
        );
    }
}
