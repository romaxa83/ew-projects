<?php

namespace Tests\Feature\Api\Contacts;

use App\Models\Locations\State;
use App\Services\TimezoneService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

abstract class ContactTestCase extends TestCase
{
    use DatabaseTransactions;

    protected function getContactFieldsUpdate(): array
    {
        return [
            'full_name' => 'tester',
            'state_id' => State::factory()->create()->id,
            'zip' => '123456',
            'type_id' => 1,
            'city' => 'Some american city',
            'address' => 'Some address',
            'phone' => '5555665555',
            'email' => $this->faker->email,
            'timezone' => resolve(TimezoneService::class)->getTimezonesArr()->pluck('timezone')->random()
        ];
    }

    protected function getContactFieldsOther(): array
    {
        return [
            'address' => 'some street 123',
            'city' => 'some city',
            'state_id' => State::factory()->create()->id,
            'phones' => [
                [
                    'number' => '123 123 1234'
                ],
                [
                    'number' => '5555665555'
                ],
            ],
        ];
    }

    protected function getContactFieldsRequired(): array
    {
        return [
            'full_name' => 'qwe123',
            'zip' => '123456',
            'type_id' => 1,
            'phone' => '5555665555',
            'email' => $this->faker->email,
            'timezone' => resolve(TimezoneService::class)->getTimezonesArr()->pluck('timezone')->random()
        ];
    }

}
