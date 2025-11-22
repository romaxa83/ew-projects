<?php

namespace Tests\Feature\Saas\GPS\DeviceRequest;

use App\Enums\Saas\GPS\Request\DeviceRequestStatus;
use App\Models\Alerts\Alert;
use App\Models\Saas\GPS\DeviceRequest;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Saas\Company\CompanyBuilder;
use Tests\Builders\Saas\GPS\DeviceRequestBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\TestCase;

class UpdateTest extends TestCase
{
    use DatabaseTransactions;

    protected DeviceRequestBuilder $deviceRequestBuilder;
    protected CompanyBuilder $companyBuilder;
    protected UserBuilder $userBuilder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->deviceRequestBuilder = resolve(DeviceRequestBuilder::class);
        $this->companyBuilder = resolve(CompanyBuilder::class);
        $this->userBuilder = resolve(UserBuilder::class);
    }

    /** @test */
    public function success_update(): void
    {
        $this->loginAsSaasSuperAdmin();

        $user = $this->userBuilder->asDriver()->create();

        /** @var $model DeviceRequest */
        $model = $this->deviceRequestBuilder->user($user)->create();

        $data = [
            'status' => DeviceRequestStatus::IN_WORK
        ];

        $this->assertFalse(Alert::query()->where('recipient_id', $user->id)->exists());

        $this->putJson(route('v1.saas.gps-devices.request-update', [$model]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'status' => $data['status'],
                    'closed_at' => null,
                    'comment' => null,
                ]
            ])
        ;

        $alert = Alert::query()->where('recipient_id', $user->id)->first();

        $this->assertEquals($alert->carrier_id, $model->company_id);
        $this->assertEquals($alert->meta, [
            'device_id' => $model->id,
            'status' => DeviceRequestStatus::IN_WORK,
        ]);


    }

    /** @test */
    public function success_update_status_closed(): void
    {
        $this->loginAsSaasSuperAdmin();

        $date = CarbonImmutable::now();
        CarbonImmutable::setTestNow($date);

        $user = $this->userBuilder->asDriver()->create();

        /** @var $model DeviceRequest */
        $model = $this->deviceRequestBuilder->user($user)->create();

        $data = [
            'status' => DeviceRequestStatus::CLOSED,
            'comment' => 'some comment'
        ];

        $this->assertFalse(Alert::query()->where('recipient_id', $user->id)->exists());

        $this->putJson(route('v1.saas.gps-devices.request-update', [$model]), $data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'status' => $data['status'],
                    'closed_at' => $date->timestamp,
                    'comment' => $data['comment'],
                ]
            ])
        ;

        $alert = Alert::query()->where('recipient_id', $user->id)->first();

        $this->assertEquals($alert->carrier_id, $model->company_id);
        $this->assertEquals($alert->meta, [
            'device_id' => $model->id,
            'status' => DeviceRequestStatus::CLOSED,
        ]);
    }

    /** @test */
    public function fail_update_status_closed_without_comment(): void
    {
        $this->loginAsSaasSuperAdmin();

        $user = $this->userBuilder->asDriver()->create();

        /** @var $model DeviceRequest */
        $model = $this->deviceRequestBuilder->user($user)->create();

        $data = [
            'status' => DeviceRequestStatus::CLOSED
        ];

        $res = $this->putJson(route('v1.saas.gps-devices.request-update', [$model]), $data)
        ;

        $this->assertResponseHasValidationMessage($res, 'comment',
            __('validation.required_if', [
                'attribute' => "Comment",
                'other' => 'status',
                'value' => DeviceRequestStatus::CLOSED
            ])
        );
    }

    /** @test */
    public function fail_update_model_is_closed(): void
    {
        $this->loginAsSaasSuperAdmin();

        $user = $this->userBuilder->asDriver()->create();

        /** @var $model DeviceRequest */
        $model = $this->deviceRequestBuilder
            ->status(DeviceRequestStatus::CLOSED())
            ->user($user)
            ->create();

        $data = [
            'status' => DeviceRequestStatus::IN_WORK
        ];

        $res = $this->putJson(route('v1.saas.gps-devices.request-update', [$model]), $data)
        ;

        $this->assertResponseHasValidationMessage($res, 'status',
            __('exceptions.gps_device.request.closed_for_editing')
        );
    }

    /** @test */
    public function not_perm(): void
    {
        $this->loginAsSaasAdmin();

        $user = $this->userBuilder->asDriver()->create();

        /** @var $model DeviceRequest */
        $model = $this->deviceRequestBuilder->user($user)->create();

        $data = [
            'status' => DeviceRequestStatus::IN_WORK
        ];

        $res = $this->putJson(route('v1.saas.gps-devices.request-update', [$model]), $data)
        ;

        $this->assertResponseUnauthorizedMessage($res);
    }
}
