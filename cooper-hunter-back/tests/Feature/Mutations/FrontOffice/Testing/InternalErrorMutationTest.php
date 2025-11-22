<?php

namespace Tests\Feature\Mutations\FrontOffice\Testing;

use App\GraphQL\Mutations\Common\Testing\InternalErrorMutation;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Tests\TestCase;

class InternalErrorMutationTest extends TestCase
{
    public const MUTATION = InternalErrorMutation::NAME;

    public function test_get_error(): void
    {
        $response = $this->postGraphQL(
            GraphQLQuery::mutation(self::MUTATION)
                ->select(
                    [
                        'message',
                    ]
                )
                ->make()
        );

        $this->assertServerError($response);
    }
}