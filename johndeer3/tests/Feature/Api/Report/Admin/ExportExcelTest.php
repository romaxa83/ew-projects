<?php

namespace Tests\Feature\Api\Report\Admin;

use App\Models\User\Role;
use App\Services\Export\ExcelService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Mockery\MockInterface;
use Tests\TestCase;
use Tests\Builder\UserBuilder;
use Tests\Traits\ResponseStructure;
use Tests\Builder\Report\ReportBuilder;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ExportExcelTest extends TestCase
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
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        Excel::fake();
        Storage::fake('public');

        CarbonImmutable::setTestNow(CarbonImmutable::now());
        $time = time();
        $path = "excel/reports_{$time}.xlsx";
        $link = config('app.url') . "/storage/{$path}";

        $this->reportBuilder->create();
        $this->reportBuilder->create();
        $this->getJson(route('api.report.export-excel'))
            ->assertJson($this->structureSuccessResponse([
                "link" => $link
            ]))
        ;

        Excel::assertStored($path, 'public');
    }

    /** @test */
    public function fail_service_return_exception()
    {
        $admin = $this->userBuilder->create();
        $this->loginAsUser($admin);

        Excel::fake();

        $this->mock(ExcelService::class, function(MockInterface $mock){
            $mock->shouldReceive("generateAndSave")
                ->andThrows(\Exception::class, "some exception message");
        });

        $this->getJson(route('api.report.export-excel'))
            ->assertJson($this->structureErrorResponse("some exception message"))
        ;
    }

    /** @test */
    public function not_admin()
    {
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $this->getJson(route('api.report.export-excel'))
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson($this->structureErrorResponse(__('message.no_access')))
        ;
    }

    /** @test */
    public function not_auth()
    {
        $this->userBuilder->create();

        $this->getJson(route('api.report.export-excel'))
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson($this->structureErrorResponse("Unauthenticated."))
        ;
    }
}
