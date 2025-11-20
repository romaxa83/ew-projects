<?php

namespace Tests\Feature\Api\Report;

use App\Models\User\Role;
use App\Models\User\User;
use App\Services\Report\ReportService;
use App\Type\ReportStatus;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery\MockInterface;
use Tests\Builder\Report\ReportBuilder;
use Tests\Builder\UserBuilder;
use Tests\Feature\Api\Report\Create\CreateTest;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;

class AttachVideoTest extends TestCase
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
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $data = CreateTest::data();

        Storage::fake('public');

        $fileName = "video_1.mp4";
        $file = UploadedFile::fake()->create(
            $fileName, 100000, 'mp4'
        );
        $data['video'] = $file;

        $report = $this->reportBuilder
            ->setStatus(ReportStatus::IN_PROCESS)
            ->setUser($user)->create();

        $this->assertNull($report->video);

        $this->postJson(route('api.report.attach-video', ['report' => $report]), $data)
            ->assertJson([
                "data" => [
                    "video" => [
                        "name" => "video_1",
                        "url" => env('APP_URL') . "/storage/video/{$report->id}/{$fileName}",
                        "download" => env('APP_URL') . "/api/report/download-video/{$report->id}"
                    ]
                ],
                "success" => true
            ])
        ;

        $report->refresh();

        $this->assertEquals($report->video->name, "video_1");
        $this->assertEquals($report->video->url, env('APP_URL') . "/storage/video/{$report->id}/{$fileName}");

        $urlPart = "video/{$report->id}/";
        Storage::disk('public')->assertExists([
            $urlPart . $fileName,
        ]);
    }

    /** @test */
    public function success_update_video()
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $data = CreateTest::data();

        Storage::fake('public');

        $fileName_1 = "video_1.mp4";
        $fileName_2 = "video_2.mp4";
        $file_1 = UploadedFile::fake()->create(
            $fileName_1, 100000, 'mp4'
        );
        $file_2 = UploadedFile::fake()->create(
            $fileName_2, 100000, 'mp4'
        );
        $data['video'] = $file_1;

        $report = $this->reportBuilder
            ->setStatus(ReportStatus::IN_PROCESS)
            ->setUser($user)->create();

        $this->postJson(route('api.report.attach-video', ['report' => $report]), $data);

        $report->refresh();

        $this->assertNotNull($report->video);
        $reportVideoName = $report->video->name;

        $urlPart = "video/{$report->id}/";
        Storage::disk('public')->assertExists([$urlPart . $fileName_1]);

        // update
        $data['video'] = $file_2;
        $this->postJson(route('api.report.attach-video', ['report' => $report]), $data);

        $report->refresh();

        $this->assertNotEquals($report->video->name, $reportVideoName);

        Storage::disk('public')->assertExists([$urlPart . $fileName_2]);
    }

    /** @test */
    public function fail_big_file()
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $data = CreateTest::data();

        Storage::fake('public');

        $fileName = "video.mp4";
        $file = UploadedFile::fake()->create(
            $fileName, 1000000, 'mp4'
        );

        $data['video'] = $file;

        $report = $this->reportBuilder
            ->setStatus(ReportStatus::IN_PROCESS)
            ->setUser($user)->create();

        $this->postJson(route('api.report.attach-video', ['report' => $report]), $data)
            ->assertJson($this->structureErrorResponse(["The video may not be greater than 300000 kilobytes."]))
        ;
    }

    /** @test */
    public function fail_empty_data()
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $data = CreateTest::data();

        $data['video'] = null;

        $report = $this->reportBuilder
            ->setStatus(ReportStatus::IN_PROCESS)
            ->setUser($user)->create();

        $this->postJson(route('api.report.attach-video', ['report' => $report]), $data)
            ->assertJson($this->structureErrorResponse(["The video field is required."]))
        ;
    }

    /** @test */
    public function fail_not_file()
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $data = CreateTest::data();

        $data['video'] = 'string';

        $report = $this->reportBuilder
            ->setStatus(ReportStatus::IN_PROCESS)
            ->setUser($user)->create();

        $this->postJson(route('api.report.attach-video', ['report' => $report]), $data)
            ->assertJson($this->structureErrorResponse(["The video must be a file."]))
        ;
    }

    /** @test */
    public function fail_not_permission()
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_ADMIN)->first();
        $role_ps = Role::query()->where('role', Role::ROLE_PS)->first();

        $user_1 = $this->userBuilder->setRole($role)->create();
        $user_2 = $this->userBuilder->setRole($role_ps)->create();
        $this->loginAsUser($user_1);

        $data = CreateTest::data();

        Storage::fake('public');

        $data['video'] = UploadedFile::fake()->create(
            "video_1.mp4", 100000, 'mp4'
        );

        $report = $this->reportBuilder
            ->setStatus(ReportStatus::IN_PROCESS)
            ->setUser($user_2)->create();

        $this->postJson(route('api.report.attach-video', ['report' => $report]), $data)
            ->assertJson($this->structureErrorResponse("This action is unauthorized."))
        ;
    }

    /** @test */
    public function fail_service_return_exception()
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $data = CreateTest::data();

        Storage::fake('public');

        $fileName = "video_1.mp4";
        $file = UploadedFile::fake()->create(
            $fileName, 100000, 'mp4'
        );
        $data['video'] = $file;

        $report = $this->reportBuilder
            ->setStatus(ReportStatus::IN_PROCESS)
            ->setUser($user)->create();

        $this->assertNull($report->video);

        $this->mock(ReportService::class, function(MockInterface $mock){
            $mock->shouldReceive("attachVideo")
                ->andThrows(\Exception::class, "some exception message");
        });

        $this->postJson(route('api.report.attach-video', ['report' => $report]), $data)
            ->assertJson($this->structureErrorResponse("some exception message"))
        ;
    }
}
