<?php

namespace Tests\Feature\Queries\FrontOffice\Stores;

use App\GraphQL\Queries\FrontOffice\Stores\DistributorQuery;
use App\Models\Locations\IpRange;
use App\Models\Locations\State;
use App\Models\Locations\Zipcode;
use App\Models\Stores\Distributor;
use App\Models\Stores\DistributorTranslation;
use App\ValueObjects\Point;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Tests\TestCase;

class DistributorQueryTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public const QUERY = DistributorQuery::NAME;

    public function test_get_list_success(): void
    {
        $this->makeDistributors();

        $query = $this->getQuery(
            [
                'coordinates' => [
                    'longitude' => 0,
                    'latitude' => 0,
                ],
            ]
        );

        $this->postGraphQL($query)
            ->assertJsonStructure(
                $this->getResponseStructure()
            );
    }

    protected function makeDistributors(): void
    {
        Distributor::factory()
            ->times(5)
            ->has(DistributorTranslation::factory()->locale(), 'translation')
            ->create();
    }

    protected function getQuery(array $args = []): array
    {
        return GraphQLQuery::query(self::QUERY)
            ->args($args)
            ->select(
                $this->getSelect()
            )
            ->make();
    }

    protected function getSelect(): array
    {
        return [
            'id',
            'active',
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

    protected function getResponseStructure(): array
    {
        return [
            'data' => [
                self::QUERY => [
                    $this->getSelect()
                ]
            ]
        ];
    }

    public function test_filter_by_zip(): void
    {
        $this->makeDistributors();

        $id = Distributor::factory()
            ->for(
                State::factory()
                    ->has(
                        Zipcode::factory()
                            ->state(
                                [
                                    'zip' => $zip = '123123123'
                                ]
                            )
                    )
            )
            ->has(DistributorTranslation::factory()->locale(), 'translation')
            ->create()
            ->id;

        $query = $this->getQuery(
            [
                'query' => $zip,
                'coordinates' => [
                    'longitude' => 0,
                    'latitude' => 0,
                ],
            ]
        );

        $distributor = $this->postGraphQL($query)
            ->assertJsonCount(1, 'data.' . self::QUERY)
            ->assertJsonStructure(
                $this->getResponseStructure()
            )
            ->json('data.' . self::QUERY . '.0');

        self::assertEquals($id, $distributor['id']);
    }

    public function test_filter_by_address(): void
    {
        $this->makeDistributors();

        $d = Distributor::factory()
            ->state(
                [
                    'address' => 'kkkkk',
                ]
            )
            ->has(DistributorTranslation::factory()->locale(), 'translation')
            ->create();

        $address = Str::substr($d->address, 0, 3);

        $query = $this->getQuery(
            [
                'query' => $address,
                'coordinates' => [
                    'longitude' => 0,
                    'latitude' => 0,
                ],
            ]
        );

        $distributor = $this->postGraphQL($query)
            ->assertJsonCount(1, 'data.' . self::QUERY)
            ->assertJsonStructure(
                $this->getResponseStructure()
            )
            ->json('data.' . self::QUERY . '.0');

        self::assertEquals($d->id, $distributor['id']);
    }

    public function test_filter_by_address_in_state(): void
    {
        $point = new Point($this->faker->longitude, $this->faker->latitude);

        $id = Distributor::factory()
            ->has(DistributorTranslation::factory()->locale(), 'translation')
            ->state(
                [
                    'coordinates' => $point
                ]
            )
            ->create()
            ->id;

        $query = $this->getQuery(
            [
                'query' => 'some non exist address for distributor, but exist address in the same State where distributor is placed',
                'coordinates' => $point->asCoordinates(),
            ]
        );

        $distributor = $this->postGraphQL($query)
            ->assertJsonCount(1, 'data.' . self::QUERY)
            ->assertJsonStructure(
                $this->getResponseStructure()
            )
            ->json('data.' . self::QUERY . '.0');

        self::assertEquals($id, $distributor['id']);
    }

    public function test_filter_by_closest_to_user_ip(): void
    {
        $ip = ip2long('127.0.0.1');

        $ipRange = IpRange::factory()
            ->state(
                [
                    'ip_from' => $ip,
                    'ip_to' => $ip,
                    'coordinates' => $coordinates = new Point(0, 0),
                    'zip' => $zip = '99999'
                ]
            )
            ->create();

        //coordinates taken from Google by user's address search
        $point = new Point($ipRange->coordinates->getLongitude(), $ipRange->coordinates->getLatitude());

        $id = Distributor::factory()
            ->coordinates($coordinates)
            ->for(
                State::factory()
                    ->has(
                        Zipcode::factory()
                            ->state(
                                compact('zip')
                            )
                    )
            )
            ->has(DistributorTranslation::factory()->locale(), 'translation')
            ->create()
            ->id;

        $query = $this->getQuery(
            [
                'query' => 'some non exist address for distributor, but exist address in the same State where distributor is placed',
                'coordinates' => $point->asCoordinates(),
            ]
        );

        $this->postGraphQL($query)
            ->assertJsonCount(1, 'data.' . self::QUERY)
            ->assertJsonStructure(
                $this->getResponseStructure()
            )
            ->assertJson(
                [
                    'data' => [
                        self::QUERY => [
                            [
                                'id' => $id
                            ]
                        ]
                    ]
                ]
            );
    }
}
