<?php

namespace Tests\Builders\Asterisk;

use App\IPTelephony\Services\Storage\Asterisk\CdrService;
use App\Models\Departments\Department;
use App\Models\Employees\Employee;
use Carbon\CarbonImmutable;
use Tests\Traits\FakerTrait;

class CdrBuilder
{
    use FakerTrait;

    protected array $data;

    public function __construct(protected CdrService $service)
    {
        $this->data = $this->getData();
    }

    private function getData(): array
    {
        return [
            'calldate' => CarbonImmutable::now(),
            'clid' => "\"Customer Support\" <Ira>",
            'src' => $this->faker()->phoneNumber,
            'dst' => $this->faker()->phoneNumber,
            'dcontext' => 'ch-in',
            'channel' => 'PJSIP/kamailio-000051bb',
            'dstchannel' => 'Local/1000@queue_members-00000095;1',
            'lastapp' => 'Queue',
            'lastdata' => '10000,,,,600',
            'duration' => 20,
            'billsec' => 10,
            'disposition' => 'ANSWERED',
            'amaflags' => 3,
            'accountcode' => '',
            'userfield' => '',
            'uniqueid' => $this->faker()->uuid,
            'employee_uuid' => null,
            'case_id' => null,
            'serial' => null,
            'department_uuid' => null,
            'is_fetch' => null,
            'true_reason_hangup' => null,
            'true_src' => null,
        ];
    }

    public function setSrc(string $value): self
    {
        $this->data['src'] = $value;
        return $this;
    }

    public function setTrueSrc(?string $value): self
    {
        $this->data['true_src'] = $value;
        return $this;
    }

    public function setDepartment(Department $model): self
    {
        $this->data['department_uuid'] = $model->guid;
        return $this;
    }

    public function setChannel(string $value): self
    {
        $this->data['channel'] = $value;
        return $this;
    }

    public function setEmployee(Employee $model): self
    {
        $this->data['employee_uuid'] = $model->guid;
        return $this;
    }

    public function setFromEmployee(Employee $model): self
    {
        $this->data['from_employee_uuid'] = $model->guid;
        return $this;
    }

    public function setLastapp(string $value): self
    {
        $this->data['lastapp'] = $value;
        return $this;
    }

    public function setClid(string $value): self
    {
        $this->data['clid'] = $value;
        return $this;
    }

    public function setIsFetching(int $value): self
    {
        $this->data['is_fetch'] = $value;
        return $this;
    }

    public function setSerialNumber(string $value): self
    {
        $this->data['serial'] = $value;
        return $this;
    }

    public function setCaseId(string $value): self
    {
        $this->data['case_id'] = $value;
        return $this;
    }

    public function setDisposition(string $value): self
    {
        $this->data['disposition'] = $value;
        return $this;
    }

    public function setTrueReasonHangup(?string $value): self
    {
        $this->data['true_reason_hangup'] = $value;
        return $this;
    }

    public function setUniqueid(string $value): self
    {
        $this->data['uniqueid'] = $value;
        return $this;
    }

    public function setCallDate(CarbonImmutable $value): self
    {
        $this->data['calldate'] = $value;
        return $this;
    }

    public function setLastData(?string $value): self
    {
        $this->data['lastdata'] = $value;
        return $this;
    }

    public function setDstchannel(?string $value): self
    {
        $this->data['dstchannel'] = $value;
        return $this;
    }

    public function setDst(string $value): self
    {
        $this->data['dst'] = $value;
        return $this;
    }

    public function setDuration(int $value): self
    {
        $this->data['duration'] = $value;
        return $this;
    }

    public function setBillsec(int $value): self
    {
        $this->data['billsec'] = $value;
        return $this;
    }

    function create(): object
    {
        $this->service->create($this->data);

        $rec = $this->service->getByFields([
            'calldate' => $this->data['calldate'],
            'uniqueid' => $this->data['uniqueid'],
        ]);

        $this->data = $this->getData();

        return $rec;
    }
}


