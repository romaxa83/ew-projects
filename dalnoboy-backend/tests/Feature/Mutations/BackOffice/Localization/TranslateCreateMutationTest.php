<?php

namespace Tests\Feature\Mutations\BackOffice\Localization;

use App\GraphQL\Mutations\BackOffice\Localization\TranslateCreateMutation;
use App\Models\Localization\Translate;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Core\Testing\GraphQL\Scalar\EnumValue;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TranslateCreateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function test_create_translate(): void
    {
        $this->loginAsAdminWithRole();

        $translate = [
            'place' => $this->faker->bothify,
            'key' => $this->faker->bothify,
            'text' => $this->faker->text,
            'lang' => new EnumValue($this->faker->language)
        ];

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TranslateCreateMutation::NAME)
                ->args(
                    [
                        'translate' => $translate
                    ]
                )
                ->select(
                    [
                        'id',
                        'place',
                        'key',
                        'text',
                        'lang',
                        'created_at',
                        'updated_at',
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        TranslateCreateMutation::NAME => [
                            'place' => $translate['place'],
                            'key' => $translate['key'],
                            'text' => $translate['text'],
                            'lang' => $translate['lang'],
                        ]
                    ]
                ]
            );

        $this->assertDatabaseHas(
            Translate::class,
            $translate
        );
    }

    public function test_try_to_create_same_translate(): void
    {
        $this->loginAsAdminWithRole();

        $translate = Translate::factory()
            ->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TranslateCreateMutation::NAME)
                ->args(
                    [
                        'translate' => [
                            'place' => $translate->place,
                            'key' => $translate->key,
                            'text' => $translate->key,
                            'lang' => new EnumValue($translate->lang)
                        ]
                    ]
                )
                ->select(
                    [
                        'id',
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'errors' => [
                        [
                            'message' => trans('validation.custom.localization.translate_exists')
                        ]
                    ]
                ]
            );
    }

}
