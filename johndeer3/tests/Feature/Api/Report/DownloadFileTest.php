<?php

namespace Tests\Feature\Api\Report;

use App\Models\User\Role;
use App\Models\User\User;
use App\Type\ReportStatus;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Builder\Report\ReportBuilder;
use Tests\Builder\UserBuilder;
use Tests\Feature\Api\Report\Create\CreateTest;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;

class DownloadFileTest extends TestCase
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

        $report = $this->reportBuilder->setStatus(ReportStatus::IN_PROCESS)
            ->setUser($user)->create();

        // загружаем файл
        $this->postJson(route('api.report.attach-video', ['report' => $report]), $data);

        $this->getJson(route('api.download-video', ['report' => $report]))
            ->assertStatus(Response::HTTP_OK)
        ;
    }
}

