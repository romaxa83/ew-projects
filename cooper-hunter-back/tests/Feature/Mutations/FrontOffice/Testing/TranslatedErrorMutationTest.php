<?php

namespace Tests\Feature\Mutations\FrontOffice\Testing;

use App\GraphQL\Mutations\Common\Testing\TranslatedErrorMutation;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Tests\TestCase;

class TranslatedErrorMutationTest extends TestCase
{
    public const MUTATION = TranslatedErrorMutation::NAME;

    public function test_get_error(): void
    {
        mt_srand(1234567);

        $response = $this->postGraphQL(
            GraphQLQuery::mutation(self::MUTATION)
                ->select(
                    [
                        'message',
                    ]
                )
                ->make()
        );

        $this->assertServerError($response, 'Be present above all else. - Naval Ravikant');
    }
}