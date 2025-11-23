<?php

namespace Tests\Feature\Ringostat\Webhook;

use App\Events\Communications\EmployeeStatus;
use App\Models\Communications\CommunicationRecord;
use App\Models\Division;
use App\Models\Ringostat\EventAfterCall;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Broadcast;
use Tests\Builders\Divisions\DivisionBuilder;
use Tests\Builders\Employees\EmployeeBuilder;
use Tests\Builders\Ringostat;
use Tests\TestCase;

class HandlerAfterCallTest extends TestCase
{
    use DatabaseTransactions;

    protected Ringostat\EventAfterCallBuilder $callBuilder;
    protected DivisionBuilder $divisionBuilder;
    protected EmployeeBuilder $employeeBuilder;

    protected array $data;

    public function setUp(): void
    {
        $this->callBuilder = resolve(Ringostat\EventAfterCallBuilder::class);
        $this->divisionBuilder = resolve(DivisionBuilder::class);
        $this->employeeBuilder = resolve(EmployeeBuilder::class);

        parent::setUp();

//        config(['broadcasting.default' => 'null']);

        $this->data = [
            "duration_conversation" => "10",
            "duration_call" => "20",
            "duration_call_ms" => "20000",
            "duration_waiting" => "10",
            "call_card_link" => "https://app.ringostat.com/project/callcards/card/1111111111.1111111/?project_id=11111",
            "type" => "in",
            "call_date" => "2022-01-01 10:16:46",
            "call_timestamp" => "1640989006505487",
            "connected_with" => "380931112233",
            "status" => "ANSWERED",
            "destination" => "380441232323",
            "number_e164" => "380931112233",
            "employee" => "John Smith",
            "employee_estension" => "101",
            "recording_presence" => "1",
            "recording" => "https://app.ringostat.com/recordings/1111111111.1111111.wav?token=",
            "recording_wav" => "https://app.ringostat.com/recordings/1111111111.1111111.wav?token=",
            "employee_id" => "111111",
            "unique_call" => "1",
            "call_id" => "1111111111.11111111111111111",
            "caller_number" => "380931112233",
            "project_id" => "110070"
        ];
    }

    /** @test */
    public function create()
    {
        $ringostatProjectId = '1111';
        /** @var $division Division */
        $division = $this->divisionBuilder
            ->misc(['ringostat_project_id' => $ringostatProjectId])
            ->create();

        $data = $this->data;
        $data['project_id'] = $ringostatProjectId;

        $this->assertEquals(0, EventAfterCall::count());
        $this->assertEquals(0, CommunicationRecord::count());

        $this->post(route('ringostat.handleAfterCall'), $data)
            ->assertJson([
                'message' => "Event recorded successfully"
            ])
        ;

        /** @var $model EventAfterCall */
        $model = EventAfterCall::first();
        /** @var $rec CommunicationRecord */
        $rec = CommunicationRecord::first();

        $this->assertEquals($model->project_id, $data['project_id']);
        $this->assertEquals($model->call_id, $data['call_id']);
        $this->assertEquals($model->type, $data['type']);
        $this->assertEquals($model->status, $data['status']);
        $this->assertEquals($model->destination, $data['destination']);
        $this->assertEquals($model->number_e164, $data['number_e164']);
        $this->assertEquals($model->caller_number, $data['caller_number']);
        $this->assertEquals($model->employee, $data['employee']);
        $this->assertEquals($model->employee_estension, $data['employee_estension']);
        $this->assertEquals($model->employee_id, $data['employee_id']);
        $this->assertEquals($model->recording_presence, $data['recording_presence']);
        $this->assertEquals($model->recording, $data['recording']);
        $this->assertEquals($model->recording_wav, $data['recording_wav']);
        $this->assertEquals($model->recording_wav, $data['recording_wav']);
        $this->assertEquals($model->duration_call, $data['duration_call']);
        $this->assertEquals($model->duration_conversation, $data['duration_conversation']);
        $this->assertEquals($model->duration_waiting, $data['duration_waiting']);
        $this->assertEquals($model->call_date, $data['call_date']);
        $this->assertEquals($model->call_timestamp, $data['call_timestamp']);

        $this->assertEquals($rec->entity->id, $model->id);
    }

    /** @test */
    public function create_detach_employee()
    {
        $employee = $this->employeeBuilder
            ->ringostat_id(123)
            ->ringostat_call_rec_id(11)
            ->create();

        $data = $this->data;
        $data['project_id'] = '11111';
        $data['employee_id'] = '123';

        $this->post(route('ringostat.handleAfterCall'), $data)
            ->assertJson([
                'message' => "Event recorded successfully"
            ])
        ;

        $employee->refresh();

        $this->assertNull($employee->ringostat_call_rec_id);

    }

    /** @test */
    public function more_one()
    {
        $this->callBuilder->create();
        $this->callBuilder->create();

        $this->assertEquals(2, EventAfterCall::count());

        $this->post(route('ringostat.handleAfterCall'), $this->data)
            ->assertJson([
                'message' => "Event recorded successfully"
            ])
        ;

        $this->assertEquals(3, EventAfterCall::count());
    }
}


