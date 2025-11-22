<?php

namespace Tests\Feature\Mutations\FrontOffice\Commercial\CommercialProjects;

use App\Events\Commercial\SendCommercialProjectToOnec;
use App\GraphQL\Mutations\FrontOffice\Commercial\CommercialProjects\CommercialProjectCreateMutation;
use App\Listeners\Commercial\SendCommercialProjectToOnecListener;
use App\Models\Commercial\CommercialProject;
use App\Models\Locations\Country;
use App\Models\Locations\State;
use App\Models\Request\Request;
use App\Models\Technicians\Technician;
use Core\Testing\GraphQL\QueryBuilder\GraphQLQuery;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class CommercialProjectCreateMutationTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    public const MUTATION = CommercialProjectCreateMutation::NAME;

    public function test_create_unique_address(): void
    {
        Event::fake([SendCommercialProjectToOnec::class]);

        $this->loginAsTechnicianWithRole();

        $data = $this->getData();

        $this->assertNull(Request::first());

        $query = $this->getQuery([
            'input' => $data
        ]);

        $id = $this->postGraphQL($query)
            ->assertOk()
            ->assertJson([
                'data' => [
                    self::MUTATION => [
                        'state' => [
                            'id' => data_get($data, 'state_id')
                        ],
                        'country' => [
                            'country_code' => data_get($data, 'country_code')
                        ]
                    ]
                ]
            ])
            ->assertJsonStructure(
                $this->getJsonStructure()
            )
            ->json('data.' . self::MUTATION . '.id');

        $project = CommercialProject::find($id);

        self::assertEquals(
            $project->full_address,
            $project->address_line_1 .', '.$project->address_line_2 .', '.$project->city .', '.$project->state()->first()->short_name .', '.$project->zip
        );

        self::assertNotNull($project->code);
        self::assertNotNull($project->address_hash);

        self::assertNull($project->previous);
        self::assertNull($project->next);

        Event::assertDispatched(function (SendCommercialProjectToOnec $event) use ($project) {
            return $event->getCommercialProject()->id === $project->id;
        });
        Event::assertListening(SendCommercialProjectToOnec::class, SendCommercialProjectToOnecListener::class);
    }

    public function getData(): array
    {
        $state = State::first();
        $country = Country::first();

        return [
            'name' => $this->faker->jobTitle,
            'address_line_1' => $this->faker->streetAddress,
            'address_line_2' => $this->faker->numerify('# floor'),
            'city' => $this->faker->city,
            'country_code' => $country->country_code,
            'state_id' => $state->id,
            'zip' => $this->faker->postcode,
            'first_name' => $this->faker->firstName,
            'last_name' => $this->faker->lastName,
            'phone' => $this->faker->e164PhoneNumber,
            'email' => $this->faker->safeEmail,
            'company_name' => $this->faker->company,
            'company_address' => $this->faker->streetAddress,
            'description' => $this->faker->text,
            'estimate_start_date' => now()->addDay()->toDateString(),
            'estimate_end_date' => now()->addMonth()->toDateString(),
        ];
    }

    public function getQuery(array $args): array
    {
        return GraphQLQuery::mutation(self::MUTATION)
            ->args($args)
            ->select(
                [
                    'id',
                    'estimate_start_date',
                    'estimate_end_date',
                    'state' => [
                        'id'
                    ],
                    'country' => [
                        'country_code'
                    ]
                ]
            )
            ->make();
    }

    public function getJsonStructure(): array
    {
        return [
            'data' => [
                self::MUTATION => [
                    'id'
                ]
            ],
        ];
    }

    public function test_create_with_exists_address(): void
    {
        $this->loginAsTechnicianWithRole();

        $data = $this->getData();

        $query = $this->getQuery(
            [
                'input' => $data
            ]
        );

        $this->postGraphQL($query)
            ->assertOk()
            ->assertJsonStructure(
                $this->getJsonStructure()
            );

        $id = $this->postGraphQL($query)
            ->assertOk()
            ->assertJsonStructure(
                $this->getJsonStructure()
            )
            ->json('data.' . self::MUTATION . '.id');

        $project = CommercialProject::find($id);

        self::assertNull($project->code);
        self::assertNotNull($project->address_hash);

        self::assertNotNull($project->previous);
        self::assertNull($project->next);
    }

    /** @test */
    public function fail_technic_not_have_certificate(): void
    {
        $this->loginAsTechnicianWithRole(
            Technician::factory()->certified()->verified()
                ->create(['is_commercial_certification' => false])
        );

        $data = $this->getData();

        $query = $this->getQuery([
            'input' => $data
        ]);

        $this->postGraphQL($query)
            ->assertJson([
                'errors' => [
                    ['message' => __("exceptions.commercial.technician does\'n have a commercial certificate")]
                ]
            ])
        ;
    }
}
