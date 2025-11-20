<?php

namespace Tests\Unit\Dto\Calls;

use App\Dto\Calls\HistoryDto;
use Tests\TestCase;

class HistoryDtoTest extends TestCase
{
    protected array $data;

    public function setUp(): void
    {
        parent::setUp();

        $this->data = [
            "calldate" => "2023-03-21 16:57:10",
            "clid" => '370" <370>',
            "src" => "370",
            "dst" => "390",
            "dcontext" => "ch",
            "channel" => "PJSIP/kamailio-000001e8",
            "dstchannel" => "PJSIP/kamailio-000001e9",
            "lastapp" => "Dial",
            "lastdata" => "PJSIP/390@kamailio,60,g",
            "duration" => 3,
            "billsec" => 0,
            "disposition" => "ANSWERED",
            "amaflags" => 3,
            "accountcode" => "",
            "userfield" => "",
            "uniqueid" => "asterisk-docker01-1679410630.5128",
            "employee_uuid" => "8adadc78-b3fe-4b89-bce1-ff8f8002692a",
            "serial" => null,
            "case_id" => null,
            "department" => "tech",
            "is_fetch" => null,
            "true_reason_hangup" => null,
            "employee_id" => 30,
            "department_id" => 14,
        ];
    }

    /** @test */
    public function success_all_data(): void
    {
        $dto = HistoryDto::byArgs($this->data);

        $this->assertEquals($dto->callDate, $this->data['calldate']);
        $this->assertEquals($dto->fromNum, $this->data['src']);
        $this->assertEquals($dto->fromName, $this->data['clid']);
        $this->assertEquals($dto->dialed, $this->data['dst']);
        $this->assertEquals($dto->duration, $this->data['duration']);
        $this->assertEquals($dto->billsec, $this->data['billsec']);
        $this->assertEquals($dto->serialNumbers, $this->data['serial']);
        $this->assertEquals($dto->caseID, $this->data['case_id']);
        $this->assertEquals($dto->lastapp, $this->data['lastapp']);
        $this->assertEquals($dto->uniqueid, $this->data['uniqueid']);
        $this->assertEquals($dto->employeeID, $this->data['employee_id']);
        $this->assertEquals($dto->departmentID, $this->data['department_id']);

        $this->assertEquals($dto->status->value, mb_strtolower($this->data['disposition']));
    }

    /** @test */
    public function success_has_status_no_answer_and_reason_empty(): void
    {
        $this->data['disposition'] = "NO ANSWER";

        $dto = HistoryDto::byArgs($this->data);

        $this->assertEquals($dto->status->value, 'no_answer');
    }
}

