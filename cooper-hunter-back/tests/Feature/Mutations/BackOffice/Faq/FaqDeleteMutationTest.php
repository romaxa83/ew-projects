<?php

namespace Tests\Feature\Mutations\BackOffice\Faq;

use App\GraphQL\Mutations\BackOffice\Faq\FaqDeleteMutation;
use App\Models\Faq\Faq;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class FaqDeleteMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = FaqDeleteMutation::NAME;

    public function test_delete_faq(): void
    {
        $this->loginAsSuperAdmin();

        $faq = Faq::factory()->create();

        $query = new GraphQLQuery(
            self::MUTATION,
            [
                'faq_id' => $faq->id,
            ]
        );

        $this->postGraphQLBackOffice($query->getMutation())
            ->assertOk()
            ->assertJsonPath('data.' . self::MUTATION, true);

        self::assertNull($faq->fresh());
    }
}
