<?php

namespace Tests\Feature\Api\GPS\Device;

use App\Enums\Saas\GPS\DeviceHistoryContext;
use App\Models\GPS\History;
use App\Models\Saas\Company\Company;
use App\Models\Saas\GPS\Device;
use App\Models\Saas\GPS\DeviceHistory;
use App\Models\Vehicles\Trailer;
use App\Models\Vehicles\Truck;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builders\Billing\InvoiceBuilder;
use Tests\Builders\Saas\Company\CompanyBuilder;
use Tests\Builders\Saas\GPS\DeviceBuilder;
use Tests\Builders\Users\UserBuilder;
use Tests\Builders\Vehicles\TrailerBuilder;
use Tests\Builders\Vehicles\TruckBuilder;
use Tests\Helpers\Traits\AdminFactory;
use Tests\Helpers\Traits\AssertErrors;
use Tests\Helpers\Traits\Permissions\PermissionFactory;
use Tests\TestCase;

class AttachVehicleTest extends TestCase
{
    use DatabaseTransactions;
    use PermissionFactory;
    use AdminFactory;
    use AssertErrors;

    protected DeviceBuilder $deviceBuilder;
    protected CompanyBuilder $companyBuilder;
    protected UserBuilder $userBuilder;
    protected TruckBuilder $truckBuilder;
    protected TrailerBuilder $trailerBuilder;
    protected InvoiceBuilder $invoiceBuilder;

    public array $connectionsToTransact = [
        'pgsql', 'pgsql_gps'
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->deviceBuilder = resolve(DeviceBuilder::class);
        $this->companyBuilder = resolve(CompanyBuilder::class);
        $this->userBuilder = resolve(UserBuilder::class);
        $this->truckBuilder = resolve(TruckBuilder::class);
        $this->trailerBuilder = resolve(TrailerBuilder::class);
        $this->invoiceBuilder = resolve(InvoiceBuilder::class);
    }

    /** @test */
    public function success_update_truck(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();
        $company = $this->companyBuilder->trial($company);

        $user = $this->userBuilder->company($company)->create();

        $this->loginAsCarrierSuperAdmin($user);

        /** @var $model Device */
        $model = $this->deviceBuilder->company($company)->create();

        /** @var $truck Truck */
        $truck = $this->truckBuilder->company($company)->create();

        $data = [
            'is_truck' => true,
            'id_vehicle' => $truck->id
        ];

        $this->assertNull($truck->gpsDevice);
        $this->assertNull($truck->last_gps_history_id);
        $this->assertNull($model->truck);

        $this->assertFalse(History::query()->where('truck_id', $truck->id)->exists());

        $this->putJson(route('gps.device-attach-vehicle-api', [$model]),$data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'truck' => [
                        'id' => $truck->id
                    ],
                ]
            ])
        ;

        $truck->refresh();

        $this->assertEquals($truck->gpsDevice->id, $model->id);

        $history = History::query()->where('truck_id', $truck->id)->first();

        $this->assertNull($truck->last_gps_history_id);
        $this->assertEquals($history->device_id, $model->id);
        $this->assertEquals($history->company_id, $company->id);
        $this->assertEquals($history->event_type, History::EVENT_ENGINE_OFF);

        $model->refresh();

        $this->assertEquals($model->histories[0]->context, DeviceHistoryContext::ATTACH_TO_VEHICLE);
    }

    /** @test */
    public function success_update_trailer(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();
        $company = $this->companyBuilder->trial($company);

        $user = $this->userBuilder->company($company)->create();

        $this->loginAsCarrierSuperAdmin($user);

        /** @var $model Device */
        $model = $this->deviceBuilder->company($company)->create();

        /** @var $trailer Trailer */
        $trailer = $this->trailerBuilder->company($company)->create();

        $data = [
            'is_truck' => false,
            'id_vehicle' => $trailer->id
        ];

        $this->assertNull($trailer->gpsDevice);
        $this->assertNull($model->trailer);
        $this->assertNull($trailer->last_gps_history_id);

        $this->putJson(route('gps.device-attach-vehicle-api', [$model]),$data)
            ->assertJson([
                'data' => [
                    'id' => $model->id,
                    'trailer' => [
                        'id' => $trailer->id
                    ],
                ]
            ])
        ;

        $trailer->refresh();

        $this->assertEquals($trailer->gpsDevice->id, $model->id);

        $history = History::query()->where('trailer_id', $trailer->id)->first();

        $this->assertNull($trailer->last_gps_history_id);
        $this->assertEquals($history->device_id, $model->id);
        $this->assertEquals($history->company_id, $company->id);
        $this->assertEquals($history->event_type, History::EVENT_ENGINE_OFF);
    }

    /** @test */
    public function fail_update_truck_has_unpaid_invoice(): void
    {
        /** @var $company Company */
        $company = $this->companyBuilder->create();
        $company = $this->companyBuilder->trial($company);
        $this->invoiceBuilder->unpaid()->company($company)->create();

        $user = $this->userBuilder->company($company)->create();

        $this->loginAsCarrierSuperAdmin($user);

        /** @var $model Device */
        $model = $this->deviceBuilder->company($company)->create();

        /** @var $truck Truck */
        $truck = $this->truckBuilder->company($company)->create();

        $data = [
            'is_truck' => true,
            'id_vehicle' => $truck->id
        ];

        $this->assertTrue($company->hasUnpaidInvoices());

        $res = $this->putJson(route('gps.device-attach-vehicle-api', [$model]),$data)
        ;

        $this->assertResponseErrorMessage($res, __('exceptions.company.billing.has_unpaid_invoice', [
            'company_name' => $company->name
        ]), 401);
    }
}


