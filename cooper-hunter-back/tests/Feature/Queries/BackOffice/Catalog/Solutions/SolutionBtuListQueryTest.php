<?php


namespace Tests\Feature\Queries\BackOffice\Catalog\Solutions;


use App\Enums\Solutions\SolutionIndoorEnum;
use App\Enums\Solutions\SolutionTypeEnum;
use App\Enums\Solutions\SolutionZoneEnum;
use App\GraphQL\Queries\BackOffice\Catalog\Solutions\SolutionBtuListQuery;
use App\Permissions\Catalog\Solutions\SolutionReadPermission;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class SolutionBtuListQueryTest extends TestCase
{
    use AdminManagerHelperTrait;
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        $this->loginByAdminManager([SolutionReadPermission::KEY]);
    }

    public function test_get_btu_single_outdoor(): void
    {
        $this->postGraphQLBackOffice(
            GraphQLQuery::query(SolutionBtuListQuery::NAME)
                ->args(
                    [
                        'type' => SolutionTypeEnum::OUTDOOR(),
                        'zone' => SolutionZoneEnum::SINGLE(),
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        SolutionBtuListQuery::NAME => config(
                            'catalog.solutions.btu.lists.' .
                            SolutionTypeEnum::OUTDOOR . '.' .
                            SolutionZoneEnum::SINGLE
                        )
                    ]
                ]
            );
    }

    public function test_get_btu_indoor(): void
    {
        $result = array_values(
            array_unique(
                array_merge(
                    config(
                        'catalog.solutions.btu.lists.' .
                        SolutionTypeEnum::INDOOR . '.' .
                        SolutionZoneEnum::SINGLE . '.' .
                        SolutionIndoorEnum::WALL_MOUNT
                    ),
                    config(
                        'catalog.solutions.btu.lists.' .
                        SolutionTypeEnum::INDOOR . '.' .
                        SolutionZoneEnum::MULTI . '.' .
                        SolutionIndoorEnum::WALL_MOUNT
                    ),
                )
            )
        );

        sort($result);

        $this->postGraphQLBackOffice(
            GraphQLQuery::query(SolutionBtuListQuery::NAME)
                ->args(
                    [
                        'type' => SolutionTypeEnum::INDOOR(),
                        'indoor_type' => SolutionIndoorEnum::WALL_MOUNT(),
                    ]
                )
                ->make()
        )
            ->assertOk()
            ->assertJson(
                [
                    'data' => [
                        SolutionBtuListQuery::NAME => $result
                    ]
                ]
            );
    }
}
