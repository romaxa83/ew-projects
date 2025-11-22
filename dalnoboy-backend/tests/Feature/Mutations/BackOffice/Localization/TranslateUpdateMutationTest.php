<?php

namespace Tests\Feature\Mutations\BackOffice\Localization;

use App\GraphQL\Mutations\BackOffice\Localization\TranslateUpdateMutation;
use App\Models\Localization\Translate;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Core\Testing\GraphQL\Scalar\EnumValue;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TranslateUpdateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public function test_update_translate(): void
    {
        $this->loginAsAdminWithRole();

        $translate = Translate::factory()
            ->create();

        $attribute = [
            'place' => $this->faker->bothify,
            'key' => $this->faker->bothify,
            'text' => $this->faker->text,
            'lang' => new EnumValue($this->faker->language)
        ];

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TranslateUpdateMutation::NAME)
                ->args(
                    [
                        'id' => $translate->id,
                        'translate' => $attribute
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
                        TranslateUpdateMutation::NAME => [
                            'id' => $translate->id,
                            'place' => $attribute['place'],
                            'key' => $attribute['key'],
                            'text' => $attribute['text'],
                            'lang' => $attribute['lang'],
                            'created_at' => $translate->created_at->getTimestamp(),
                            'updated_at' => $translate->updated_at->getTimestamp(),
                        ]
                    ]
                ]
            );
    }

    public function test_try_to_update_same_translate(): void
    {
        $this->loginAsAdminWithRole();

        $translate = Translate::factory()
            ->create();

        $this->postGraphQLBackOffice(
            GraphQLQuery::mutation(TranslateUpdateMutation::NAME)
                ->args(
                    [
                        'id' => Translate::factory()
                            ->create()->id,
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
