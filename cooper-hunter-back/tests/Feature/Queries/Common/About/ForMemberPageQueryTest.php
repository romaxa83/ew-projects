<?php

namespace Tests\Feature\Queries\Common\About;

use App\Enums\About\ForMemberPageEnum;
use App\GraphQL\Queries\Common\About\BaseForMemberPageQuery;
use App\Models\About\ForMemberPage;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ForMemberPageQueryTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = BaseForMemberPageQuery::NAME;

    public function test_rebates_page(): void
    {
        $type = ForMemberPage::factory()
            ->type(ForMemberPageEnum::REBATES())
            ->create();

        $query = $this->getQuery(ForMemberPageEnum::REBATES());

        $this->postGraphQL($query)
            ->assertJson($this->getJsonAssertion($type));
    }

    public function test_success_front_office(): void
    {
        $for = ForMemberPage::factory()
            ->forHomeowner()
            ->create();

        $query = $this->getQuery(ForMemberPageEnum::FOR_HOMEOWNER());

        $this->postGraphQL($query)
            ->assertJson($this->getJsonAssertion($for));
    }

    protected function getQuery(ForMemberPageEnum $for): array
    {
        return GraphQLQuery::query(self::QUERY)
            ->args(
                [
                    'for_member_type' => $for
                ]
            )
            ->select(
                [
                    'id',
                    'for_member_type',
                ]
            )
            ->make();
    }

    protected function getJsonAssertion(ForMemberPage $for): array
    {
        return [
            'data' => [
                self::QUERY => [
                    'id' => $for->id,
                    'for_member_type' => $for->for_member_type->value
                ],
            ],
        ];
    }

    public function test_success_back_office(): void
    {
        $this->loginAsSuperAdmin();

        $for = ForMemberPage::factory()
            ->forTechnician()
            ->create();

        $query = $this->getQuery(ForMemberPageEnum::FOR_TECHNICIAN());

        $this->postGraphQLBackOffice($query)
            ->assertJson($this->getJsonAssertion($for));
    }
}
