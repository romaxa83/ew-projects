<?php

namespace Tests\Feature\Api\Statistics;

use App\Helpers\DateFormat;
use App\Models\JD\Dealer;
use App\Models\JD\ModelDescription;
use App\Models\Report\Report;
use App\Models\User\Role;
use App\Models\User\User;
use App\Type\ReportStatus;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builder\Report\ReportBuilder;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;

class ForMachineTest extends TestCase
{
    use DatabaseTransactions;
    use ResponseStructure;

    protected $userBuilder;
    protected $reportBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
        $this->userBuilder = resolve(UserBuilder::class);
        $this->reportBuilder = resolve(ReportBuilder::class);
    }

    /** @test */
    public function success()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        $year = Carbon::now()->year;
        $country = 'Ukraine';

        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();

        /** @var $dealer Dealer */
        $dealer = Dealer::query()->first();

        /** @var $user User */
        $user = $this->userBuilder
            ->setRole($role)
            ->setDealer($dealer)
            ->create();

        /** @var $modelDescriptions ModelDescription */
        $modelDescriptions = ModelDescription::query()->first();

        $report = $this->reportBuilder
            ->setStatus(ReportStatus::CREATED)
            ->setModelDescription($modelDescriptions)
            ->setCountry($country)
            ->setUser($user)
            ->create();

        $data = [
            'dealerId' => $dealer->id,
            'eg' => $modelDescriptions->equipmentGroup->id,
            'md' => $modelDescriptions->id,
            'country' => $country,
            'year' => $year,
        ];

        $this->getJson(route('api.statistic.machine', $data))
            ->assertJson(['data' => [
                0 => [
                    'id' =>$report->id
                ]
            ]])
        ;
    }
}




