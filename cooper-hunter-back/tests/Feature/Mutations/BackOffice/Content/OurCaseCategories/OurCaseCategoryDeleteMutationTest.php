<?php

namespace Tests\Feature\Mutations\BackOffice\Content\OurCaseCategories;

use App\GraphQL\Mutations\BackOffice\Content\OurCaseCategories\OurCaseCategoryDeleteMutation;
use App\Models\Content\OurCases\OurCaseCategory;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class OurCaseCategoryDeleteMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = OurCaseCategoryDeleteMutation::NAME;

    public function test_delete(): void
    {
        $this->loginAsSuperAdmin();

        $category = OurCaseCategory::factory()
            ->create();

        $query = new GraphQLQuery(
            self::MUTATION,
            [
                'id' => $category->id,
            ]
        );

        $this->postGraphQLBackOffice($query->getMutation())
            ->assertJsonPath('data.' . self::MUTATION, true);

        $this->assertDatabaseMissing(
            OurCaseCategory::TABLE,
            [
                'id' => $category->id,
            ]
        );
    }
}
