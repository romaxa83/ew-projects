<?php

namespace Tests\Unit\IPTelephony\Services\Storage\Asterisk;

use App\IPTelephony\Services\Storage\Asterisk\QueueMemberService;
use App\Models\Employees\Employee;
use App\Models\Sips\Sip;
use Tests\Builders\Employees\EmployeeBuilder;
use Tests\Builders\Sips\SipBuilder;
use Tests\TestCase;

class QueueMemberServiceTest extends TestCase
{
    private QueueMemberService $service;
    protected EmployeeBuilder $employeeBuilder;
    protected SipBuilder $sipBuilder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = $this->app->make(QueueMemberService::class);
        $this->sipBuilder = resolve( SipBuilder::class);
        $this->employeeBuilder = resolve(EmployeeBuilder::class);
    }

    /** @test */
    public function prepare_data_for_insert(): void
    {
        /** @var $sip Sip */
        $sip = $this->sipBuilder->create();
        /** @var $model Employee */
        $model = $this->employeeBuilder->setSip($sip)->create();

        $data = $this->service->prepareDataForInsert($model);

        $this->assertEquals(data_get($data, 'queue_name'), $model->department->name);
        $this->assertEquals(data_get($data, 'uuid'), $model->guid);
        $this->assertEquals(data_get($data, 'membername'), $sip->number);
        $this->assertEquals(data_get($data, 'interface'), 'Local/' . $sip->number . '@queue_members');
        $this->assertEquals(data_get($data, 'state_interface'), 'Custom:' . $sip->number);
        $this->assertEquals(data_get($data, 'wrapuptime'), config('asterisk.queue_member.wrapuptime'));
        $this->assertEquals(data_get($data, 'ringinuse'), config('asterisk.queue_member.ringinuse'));
    }
}
