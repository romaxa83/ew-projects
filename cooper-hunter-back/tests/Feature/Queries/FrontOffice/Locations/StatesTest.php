<?php

namespace Tests\Feature\Queries\FrontOffice\Locations;

use App\GraphQL\Queries\FrontOffice\Locations\States;
use App\Models\Locations\State;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class StatesTest extends TestCase
{
    use DatabaseTransactions;

    public const QUERY = States::NAME;

    public function test_get_list_of_states(): void
    {
        $this->loginAsTechnician();

        $query = $this->getQuery();

        $this->postGraphQL(compact('query'))
            ->assertOk()
            ->assertJsonCount(
                State::query()->where('status', true)->count(),
                'data.'.self::QUERY
            )
            ->assertJsonStructure(
                $this->getJsonStructure()
            );
    }

    protected function getQuery(?string $args = null): string
    {
        return sprintf(
            'query { %s
                %s
                {
                    name
                    short_name
                    published
                    requires_hvac_license
                    requires_epa_license
                    translations {
                        id
                        name
                        language
                    }
                }
            }',
            self::QUERY,
            $args,
        );
    }

    protected function getJsonStructure(): array
    {
        return [
            'data' => [
                self::QUERY => [
                    [
                        'name',
                        'short_name',
                        'published',
                        'requires_hvac_license',
                        'requires_epa_license',
                        'translations' => [
                            [
                                'id',
                                'name',
                                'language',
                            ]
                        ],
                    ]
                ],
            ]
        ];
    }

    public function test_get_filtered_list_of_states(): void
    {
        $this->loginAsTechnician();

        $query = $this->getQuery('(name: "alaba")');

        $this->postGraphQL(compact('query'))
            ->assertJsonCount(1, 'data.'.self::QUERY)
            ->assertOk()
            ->assertJsonStructure(
                $this->getJsonStructure()
            );

        /** @var State $state */
        $state = State::query()->getQuery()->select('id')->first();

        $query = $this->getQuery(sprintf('(id: "%s")', $state->id));

        $this->postGraphQL(compact('query'))
            ->assertJsonCount(1, 'data.'.self::QUERY)
            ->assertOk();
    }
}
