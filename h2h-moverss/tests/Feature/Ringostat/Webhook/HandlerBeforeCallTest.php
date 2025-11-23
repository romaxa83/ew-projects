<?php

namespace Tests\Feature\Ringostat\Webhook;

use App\Models\Client;
use App\Models\Communications\CommunicationRecord;
use App\Models\Ringostat\EventBeforeCall;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Clients\ClientBuilder;
use Tests\Builders\Clients\PhoneBuilder;
use Tests\Builders\Divisions\DivisionBuilder;
use Tests\Builders\Employees\EmployeeBuilder;
use Tests\Builders\Ringostat;
use Tests\TestCase;

class HandlerBeforeCallTest extends TestCase
{
    use DatabaseTransactions;

    protected ClientBuilder $clientBuilder;
    protected PhoneBuilder $clientPhoneBuilder;
    protected EmployeeBuilder $employeeBuilder;
    protected DivisionBuilder $divisionBuilder;
    protected Ringostat\EventBeforeCallBuilder $callBuilder;

    protected array $data;

    public function setUp(): void
    {
        $this->clientPhoneBuilder = resolve(PhoneBuilder::class);
        $this->clientBuilder = resolve(ClientBuilder::class);
        $this->employeeBuilder = resolve(EmployeeBuilder::class);
        $this->callBuilder = resolve(Ringostat\EventBeforeCallBuilder::class);
        $this->divisionBuilder = resolve(DivisionBuilder::class);

        parent::setUp();

        $this->data = [
            "call_type" => "in",
            "call_date_microsecond" => "1728372216699252",
            "destination" => "+380954514991",
            "number_e164" => "+380954514992",
            "callers_number" => "+380954514994",
            "call_date" => "2024-10-08 17:53:03",
            "call_id" => "us1_-1728427983.1099010",
        ];
    }

    /** @test */
    public function create()
    {
        $data = $this->data;
        $data['project_id'] = '111111';

        $this->assertEquals(0, EventBeforeCall::count());
        $this->assertEquals(0, CommunicationRecord::count());

        $this->post(route('ringostat.handleBeforeCall'), $data)
            ->assertJson([
                'message' => "Event recorded successfully"
            ])
        ;

        $this->assertEquals(0, CommunicationRecord::count());

        /** @var $model EventBeforeCall */
        $model = EventBeforeCall::first();

        $this->assertEquals($model->project_id, $data['project_id']);
        $this->assertEquals($model->call_type, $data['call_type']);
        $this->assertEquals($model->call_date_microsecond, $data['call_date_microsecond']);
        $this->assertEquals($model->destination, $data['destination']);
        $this->assertEquals($model->number_e164, $data['number_e164']);
        $this->assertEquals($model->callers_number, $data['callers_number']);
        $this->assertNull($model->employee_ringostat_id);
        $this->assertNull($model->client_id);
        $this->assertEquals($model->call_date, $data['call_date']);
        $this->assertEquals($model->call_id, $data['call_id']);
        $this->assertNull($model->extension_number);
        $this->assertNull($model->responsible_employees);
    }

    /** @test */
    public function create_attach_client_by_callers_number_if_type_in()
    {
        $number = '232323232';

        /** @var $client Client */
        $client = $this->clientBuilder->create();

        $this->clientPhoneBuilder->value($number)
            ->client($client)->create();
        $this->clientPhoneBuilder->client($client)->create();

        $data = $this->data;
        $data['project_id'] = 1111;
        $data['callers_number'] = '+'.$number;

        $this->post(route('ringostat.handleBeforeOutCall'), $data)
            ->assertJson([
                'message' => "Event recorded successfully"
            ])
        ;

        /** @var $model EventBeforeCall */
        $model = EventBeforeCall::first();

        $this->assertEquals($model->client_id, $client->id);
    }
}


