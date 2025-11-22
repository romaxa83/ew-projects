<?php

namespace Tests\Feature\Mutations\BackOffice\Localization;

use App\GraphQL\Mutations\Common\Localization\BaseSetLanguageMutation;
use App\Models\Admins\Admin;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Core\Testing\GraphQL\Scalar\EnumValue;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SetLanguageMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function test_change_admin_language(): void
    {
        $admin = $this->loginAsAdminWithRole();
        $language = $this->faker->language;

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(BaseSetLanguageMutation::NAME)
                ->args(
                    [
                        'lang' => new EnumValue($language)
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        BaseSetLanguageMutation::NAME => true
                    ]
                ]
            );

        $this->assertDatabaseHas(
            Admin::class,
            [
                'id' => $admin->id,
                'lang' => $language
            ]
        );
    }
}
