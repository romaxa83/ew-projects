<?php

declare(strict_types=1);

namespace Tests\Feature\Queries\BackOffice\Stores;

use App\GraphQL\Queries\BackOffice\Stores\DistributorQuery;
use App\Models\Stores\Distributor;
use App\Models\Stores\DistributorTranslation;
use App\ValueObjects\Point;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DistributorQueryTest extends TestCase
{
    use DatabaseTransactions;

    public function test_get_list_success(): void
    {
        $this->loginAsSuperAdmin();

        Distributor::factory()
            ->times(5)
            ->has(DistributorTranslation::factory()->locale(), 'translation')
            ->create();

        $query = $this->getQuery();

        $this->postGraphQLBackOffice($query)
            ->assertOk()
            ->assertJsonStructure(
                [
                    'data' => [
                        DistributorQuery::NAME => [
                            $this->getSelect()
                        ],
                    ]
                ]
            );
    }

    protected function getQuery(array $args = []): array
    {
        return GraphQLQuery::query(DistributorQuery::NAME)
            ->args($args)
            ->select($this->getSelect())
            ->make();
    }

    protected function getSelect(): array
    {
        return [
            'id',
            'active',
            'state' => [
                'id',
                'name',
            ],
            'coordinates' => [
                'longitude',
                'latitude',
            ],
            'address',
            'link',
            'phone',
            'translation' => [
                'id',
                'language',
                'title',
            ],
        ];
    }

    public function test_filter_by_coordinates_in_radius(): void
    {
        $this->loginAsSuperAdmin();

        /*
         * tried to build all point on one line (horizontally)
         */

        //a starting point
        $this->createDistributorOnCoordinates(/*$p0 = */ new Point(-0.00591, 0.00477));

        //80.49 kilometers distance
        $this->createDistributorOnCoordinates(/*$p80 = */ new Point(-0.73054, 0.00171));

        //85 kilometers distance
        $this->createDistributorOnCoordinates(/*$p85 = */ new Point(-0.77033, 0.00095));

        //90.02 kilometers distance
        $this->createDistributorOnCoordinates(/*$p90 = */ new Point(-0.81548, 0.00095));

        //filter by 10 km radius relative to the starting point
        $p10 = new Point(0.0840223, 0.00477);

        $query = $this->getQuery(
            [
                'radius' => $p10->asCoordinatesWithRadius(10),
            ]
        );
        $this->assertFilterByRadius($query, 1);

        $query = $this->getQuery(
            [
                'radius' => $p10->asCoordinatesWithRadius(100),
            ]
        );
        $this->assertFilterByRadius($query, 3);

        $query = $this->getQuery(
            [
                'radius' => $p10->asCoordinatesWithRadius(110),
            ]
        );
        $this->assertFilterByRadius($query, 4);
    }

    protected function createDistributorOnCoordinates(Point $point): void
    {
        Distributor::factory()
            ->coordinates($point)
            ->has(DistributorTranslation::factory()->locale(), 'translation')
            ->create();
    }

    protected function assertFilterByRadius(array $query, int $count): void
    {
        $this->postGraphQLBackOffice($query)
            ->assertOk()
            ->assertJsonCount($count, 'data.' . DistributorQuery::NAME)
            ->assertJsonStructure(
                [
                    'data' => [
                        DistributorQuery::NAME => [
                            $this->getSelect()
                        ],
                    ]
                ]
            );
    }
}
