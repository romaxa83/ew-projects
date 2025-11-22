<?php

namespace Tests\Feature\Mutations\BackOffice\Commercial\CommercialProjects;

use App\Enums\Formats\DatetimeEnum;
use App\Events\Commercial\SendCommercialProjectToOnec;
use App\GraphQL\Mutations\BackOffice\Commercial\CommercialProjects\CommercialProjectUpdateMutation;
use App\Listeners\Commercial\SendCommercialProjectToOnecListener;
use App\Models\Commercial\CommercialProject;
use App\Models\Locations\Country;
use App\Models\Locations\State;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class CommercialProjectUpdateMutationTest extends TestCase
{
    use DatabaseTransactions;

    public const MUTATION = CommercialProjectUpdateMutation::NAME;

    public function test_update_request_until_time(): void
    {
        $this->loginAsSuperAdmin();

        Event::fake([SendCommercialProjectToOnec::class]);

        $project = CommercialProject::factory()
            ->requestIsExpired()
            ->create();

        $country = Country::query()->where('id', '!=', $project->country_id)->first();
        $state = State::query()->where('id', '!=', $project->state_id)->first();

        $newDate = [
            'id' => $project->id,
            'name' => 'update name',
            'address_line_1' => 'update address_line_1',
            'address_line_2' => 'update address_line_2',
            'city' => 'update city',
            'state_id' => $state->id,
            'country_code' => $country->country_code,
            'zip' => 'update zip',
            'first_name' => 'update first_name',
            'last_name' => 'update last_name',
            'phone' => '18899985876',
            'email' => 'update@gmail.com',
            'company_name' => 'update company_name',
            'company_address' => 'update company_address',
            'description' => 'update description',
            'estimate_start_date' => CarbonImmutable::now()->addDay()->format('Y-m-d'),
            'estimate_end_date' => CarbonImmutable::now()->addDays(3)->format('Y-m-d'),
            'request_until' => CarbonImmutable::now()->addDay()->format('Y-m-d'),
        ];

        self::assertNotEquals($newDate['name'], $project->name);
        self::assertNotEquals($newDate['address_line_1'], $project->address_line_1);
        self::assertNotEquals($newDate['address_line_2'], $project->address_line_2);
        self::assertNotEquals($newDate['city'], $project->city);
        self::assertNotEquals($newDate['state_id'], $project->state_id);
        self::assertNotEquals($country->id, $project->country_id);
        self::assertNotEquals($newDate['zip'], $project->zip);
        self::assertNotEquals($newDate['first_name'], $project->first_name);
        self::assertNotEquals($newDate['last_name'], $project->last_name);
        self::assertNotEquals($newDate['phone'], $project->phone);
        self::assertNotEquals($newDate['email'], $project->email);
        self::assertNotEquals($newDate['company_name'], $project->company_name);
        self::assertNotEquals($newDate['company_address'], $project->company_address);
        self::assertNotEquals($newDate['description'], $project->description);
        self::assertNotEquals($newDate['estimate_start_date'], $project->estimate_start_date->format(DatetimeEnum::DATE));
        self::assertNotEquals($newDate['estimate_end_date'], $project->estimate_end_date->format(DatetimeEnum::DATE));
        self::assertNotEquals($newDate['request_until'], $project->request_until->format(DatetimeEnum::DATE));


        $this->postGraphQLBackOffice([
            'query' => $this->getQueryStr($newDate)
        ])
            ->assertJson(
                [
                    'data' => [
                        self::MUTATION => [
                            'name' => $newDate['name'],
                            'address_line_1' => $newDate['address_line_1'],
                            'address_line_2' => $newDate['address_line_2'],
                            'city' => $newDate['city'],
                            'state' => [
                                'id' => $state->id
                            ],
                            'country' => [
                                'id' => $country->id
                            ],
                            'zip' => $newDate['zip'],
                            'first_name' => $newDate['first_name'],
                            'last_name' => $newDate['last_name'],
                            'phone' => $newDate['phone'],
                            'email' => $newDate['email'],
                            'company_name' => $newDate['company_name'],
                            'company_address' => $newDate['company_address'],
                            'description' => $newDate['description'],
                            'estimate_end_date' => $newDate['estimate_end_date'],
                            'estimate_start_date' => $newDate['estimate_start_date'],
                            'request_until' => $newDate['request_until'],
                        ],
                    ]
                ]
            )
        ;

        Event::assertDispatched(function (SendCommercialProjectToOnec $event) use ($project) {
            return $event->getCommercialProject()->id === $project->id;
        });
        Event::assertListening(SendCommercialProjectToOnec::class, SendCommercialProjectToOnecListener::class);
    }

    protected function getQueryStr(array $data): string
    {
        return sprintf(
            '
            mutation {
                %s (
                    id: %s,
                    input: {
                        name: "%s"
                        address_line_1: "%s"
                        address_line_2: "%s"
                        city: "%s"
                        state_id: "%s"
                        country_code: "%s"
                        zip: "%s"
                        first_name: "%s"
                        last_name: "%s"
                        phone: "%s"
                        email: "%s"
                        company_name: "%s"
                        company_address: "%s"
                        description: "%s"
                        estimate_start_date: "%s"
                        estimate_end_date: "%s"
                        request_until: "%s"
                    },
                ) {
                    id
                    name
                    address_line_1
                    address_line_2
                    city
                    state {
                        id
                    }
                    country {
                        id
                    }
                    zip
                    first_name
                    last_name
                    phone
                    email
                    company_name
                    company_address
                    description
                    estimate_start_date
                    estimate_end_date
                    request_until
                }
            }',
            self::MUTATION,
            $data['id'],
            $data['name'],
            $data['address_line_1'],
            $data['address_line_2'],
            $data['city'],
            $data['state_id'],
            $data['country_code'],
            $data['zip'],
            $data['first_name'],
            $data['last_name'],
            $data['phone'],
            $data['email'],
            $data['company_name'],
            $data['company_address'],
            $data['description'],
            $data['estimate_start_date'],
            $data['estimate_end_date'],
            $data['request_until'],
        );
    }
}
