<?php


namespace Tests\Feature\Queries\BackOffice\Branches;


use App\GraphQL\Queries\BackOffice\Branches\BranchesListQuery;
use App\Models\Branches\Branch;
use App\Models\Locations\Region;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class BranchesListQueryTest extends TestCase
{
    use DatabaseTransactions;

    /**@var Branch[] $branches */
    private iterable $branches;

    public function setUp(): void
    {
        parent::setUp();

        $this->branches[] = Branch::factory()
            ->create(
                [
                    'region_id' => Region::whereSlug('kyiv-city')
                        ->first()->id,
                    'name' => 'abcd',
                    'city' => 'Kiev'
                ]
            );

        $this->branches[] = Branch::factory()
            ->create(
                [
                    'region_id' => Region::whereSlug('kharkiv')
                        ->first()->id,
                    'name' => 'abcd',
                    'city' => 'Kharkiv'
                ]
            );

        $this->branches[] = Branch::factory()
            ->create(
                [
                    'region_id' => Region::whereSlug('donetsk')
                        ->first()->id,
                    'name' => 'abcd',
                    'city' => 'Donetsk'
                ]
            );

        $this->branches[] = Branch::factory()
            ->create(
                [
                    'region_id' => Region::whereSlug('donetsk')
                        ->first()->id,
                    'name' => 'bcd',
                    'city' => 'Donetsk'
                ]
            );

        $this->branches[] = Branch::factory()
            ->create(
                [
                    'region_id' => Region::whereSlug('donetsk')
                        ->first()->id,
                    'name' => 'bcd',
                    'city' => 'Makeevka'
                ]
            );

        $this->loginAsAdminWithRole();
    }

    public function test_get_all_list(): void
    {
        $this->postGraphQLBackOffice(
            GraphQLQuery::query(BranchesListQuery::NAME)
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
                    'data' => [
                        BranchesListQuery::NAME => [
                            [
                                'id' => $this->branches[0]->id
                            ],
                            [
                                'id' => $this->branches[1]->id
                            ],
                            [
                                'id' => $this->branches[2]->id
                            ],
                            [
                                'id' => $this->branches[3]->id
                            ],
                            [
                                'id' => $this->branches[4]->id
                            ],
                        ]
                    ]
                ]
            );
    }
}
