<?php

namespace Tests\Feature\Mutations\FrontOffice\Localization;

use App\GraphQL\Mutations\Common\Localization\BaseSetLanguageMutation;
use App\Models\Users\User;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Core\Testing\GraphQL\Scalar\EnumValue;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SetLanguageMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function test_change_user_language(): void
    {
        $user = $this->loginAsUserWithRole();
        $language = $this->faker->language;

        $this->postGraphQL(
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
            User::class,
            [
                'id' => $user->id,
                'lang' => $language
            ]
        );
    }
}
