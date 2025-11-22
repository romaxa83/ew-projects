<?php

namespace Tests\Unit\Events\Alerts\Dealer;

use App\Dto\Dealers\DealerRegisterDto;
use App\Enums\Alerts\AlertDealerEnum;
use App\Enums\Alerts\AlertModelEnum;
use App\Models\Admins\Admin;
use App\Models\Alerts\Alert;
use App\Models\Alerts\AlertRecipient;
use App\Models\Companies\Company;
use App\Models\Dealers\Dealer;
use App\Services\Dealers\DealerService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Builders\Company\CompanyBuilder;
use Tests\Builders\Dealers\DealerBuilder;
use Tests\TestCase;
use Tests\Traits\Permissions\AdminManagerHelperTrait;

class DealerRegisteredEventTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;
    use AdminManagerHelperTrait;

    protected CompanyBuilder $companyBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->companyBuilder = resolve(CompanyBuilder::class);
    }

    public function test_registration(): void
    {
        /** @var $service DealerService */
        $service = resolve(DealerService::class);

        /** @var $company Company */
        $company = $this->companyBuilder->create();

        $admin = Admin::factory()->create();

        $dealer = $service->register(
            DealerRegisterDto::byArgs([
                'email' => $this->faker->email,
                'password' => $this->faker->password,
            ]),
            $company
        );

        $this->assertDatabaseHas(Alert::class, [
            'type' => AlertModelEnum::DEALER . '_' . AlertDealerEnum::REGISTRATION,
            'model_id' => $dealer->id,
            'model_type' => $dealer::MORPH_NAME
        ]);

        $this->assertDatabaseHas(AlertRecipient::class, [
            'recipient_id' => $admin->id,
            'recipient_type' => $admin::MORPH_NAME
        ]);
    }
}
