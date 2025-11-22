<?php

namespace Tests\Feature\Mutations\FrontOffice\Faq\Questions;

use App\GraphQL\Mutations\FrontOffice\Faq\Questions\AskAQuestionMutation;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AskAQuestionMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public const MUTATION = AskAQuestionMutation::NAME;

    public function test_ask_a_question(): void
    {
        $query = GraphQLQuery::mutation(self::MUTATION)
            ->args(
                [
                    'input' => $this->getQuestion()
                ]
            )
            ->select(
                [
                    'message'
                ]
            )
            ->make();

        $this->postGraphQL($query)
            ->assertJson(
                [
                    'data' => [
                        self::MUTATION => [
                            'message' => __('Thanks for the question. Our manager will contact you soon')
                        ],
                    ],
                ]
            );
    }

    protected function getQuestion(): array
    {
        return [
            'name' => $this->faker->lastName,
            'email' => $this->faker->safeEmail,
            'question' => $this->faker->sentence,
        ];
    }
}
