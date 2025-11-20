<?php

namespace Tests\Builders\Asterisk;

use App\IPTelephony\Services\Storage\Asterisk\QueueLogService;
use App\Models\Departments\Department;
use App\Models\Sips\Sip;
use Carbon\CarbonImmutable;
use Tests\Traits\FakerTrait;

class QueueLogBuilder
{
    use FakerTrait;

    protected array $data;

    public function __construct(protected QueueLogService $service)
    {
        $this->data = $this->getData();
    }

    private function getData(): array
    {
        return [
            'time' => CarbonImmutable::now(),
            'callid' => CarbonImmutable::now(),
            'queuename' => 'tech',
            'agent' => '370',
            'event' => 'ENTERQUEUE',
            'data1' => '',
            'data2' => '',
            'data3' => '',
            'data4' => '',
            'data5' => '',
        ];
    }

    public function setTime(CarbonImmutable $value): self
    {
        $this->data['time'] = $value;
        return $this;
    }

    public function setCallid(string $value): self
    {
        $this->data['callid'] = $value;
        return $this;
    }

    public function setQueuename(Department $model): self
    {
        $this->data['queuename'] = $model->name;
        return $this;
    }

    public function setAgent(Sip|string $model): self
    {
        if($model instanceof Sip){
            $this->data['agent'] = $model->number;
        } else {
            $this->data['agent'] = $model;
        }
        return $this;
    }

    public function setEvent(string $value): self
    {
        $this->data['event'] = $value;
        return $this;
    }

    public function setData1(string $value): self
    {
        $this->data['data1'] = $value;
        return $this;
    }

    public function setData2(string $value): self
    {
        $this->data['data2'] = $value;
        return $this;
    }

    public function setData3(string $value): self
    {
        $this->data['data3'] = $value;
        return $this;
    }

    public function setData4(string $value): self
    {
        $this->data['data4'] = $value;
        return $this;
    }

    public function setData5(string $value): self
    {
        $this->data['data5'] = $value;
        return $this;
    }

    public function setFetch(string $value): self
    {
        $this->data['is_fetch'] = $value;
        return $this;
    }

    function create(): object
    {
        $this->service->create($this->data);

        $rec = $this->service->getByFields([
            'time' => $this->data['time'],
            'callid' => $this->data['callid'],
        ]);

        $this->data = $this->getData();

        return $rec;
    }
}
