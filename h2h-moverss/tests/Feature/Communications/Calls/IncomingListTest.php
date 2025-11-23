<?php

namespace Tests\Feature\Communications\Calls;

use App\Models\Calls\IncomingCall;
use App\Models\Client;
use App\Models\Division;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Calls\IncomingCallBuilder;
use Tests\Builders\Clients\ClientBuilder;
use Tests\Builders\Divisions\DivisionBuilder;
use Tests\TestCase;

class IncomingListTest extends TestCase
{
    use DatabaseTransactions;

    protected ClientBuilder $clientBuilder;
    protected DivisionBuilder $divisionBuilder;
    protected IncomingCallBuilder $incomingCallBuilder;

    protected array $data;

    public function setUp(): void
    {
        $this->clientBuilder = resolve(ClientBuilder::class);
        $this->divisionBuilder = resolve(DivisionBuilder::class);
        $this->incomingCallBuilder = resolve(IncomingCallBuilder::class);

        parent::setUp();
    }

    /** @test */
    public function success_get_one_record()
    {
        $this->loginUser();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        /** @var $client Client */
        $client = $this->clientBuilder->create();

        /** @var $model IncomingCall */
        $model = $this->incomingCallBuilder
            ->client($client)
            ->create();

        $this->get(route('communications.incoming-calls'))
            ->assertJson([
                'success' => true,
                'records' => [
                    [
                        'id' => $model->id,
                        'provider' => $model->provider->value,
                        'phone' => $model->phone,
                        'created_at' => $model->created_at->timestamp,
                        'client' => [
                            'id' => $client->id,
                            'first_name' => $client->name,
                            'last_name' => $client->lname,
                        ]
                    ],
                ]
            ])
            ->assertJsonCount(1, 'records')
        ;
    }

    /** @test */
    public function success_get_list()
    {
        $date =CarbonImmutable::now();

        $this->loginUser();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        /** @var $model_1 IncomingCall */
        $model_1 = $this->incomingCallBuilder
            ->created_at($date)
            ->create();

        $model_2 = $this->incomingCallBuilder
            ->created_at($date->addMinutes(10))->create();
        $model_3 = $this->incomingCallBuilder
            ->created_at($date->addMinutes(8))->create();

        $this->get(route('communications.incoming-calls'))
            ->assertJson([
                'success' => true,
                'records' => [
                    ['id' => $model_2->id],
                    ['id' => $model_3->id],
                    ['id' => $model_1->id],
                ]
            ])
            ->assertJsonCount(3, 'records')
        ;
    }

    /** @test */
    public function success_empty_data()
    {
        $this->loginUser();

        /** @var $division Division */
        $division = $this->divisionBuilder->create();
        $this->session(['division' => $division->toArray()]);

        $this->get(route('communications.incoming-calls'))
            ->assertJson([
                'success' => true,
                'records' => []
            ])
            ->assertJsonCount(0, 'records')
        ;
    }

}

