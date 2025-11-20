<?php

namespace Tests\Unit\Traits;

use App\Models\Report\Report;
use App\Models\Report\Video;
use App\Models\User\User;
use App\Traits\StoragePath;
use App\Type\ClientType;
use App\Type\ReportStatus;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builder\Report\ReportBuilder;
use Tests\Builder\UserBuilder;
use Tests\TestCase;

class StoragePathTest extends TestCase
{
    use DatabaseTransactions;

    protected $userBuilder;
    protected $reportBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->userBuilder = resolve(UserBuilder::class);
        $this->reportBuilder = resolve(ReportBuilder::class);
    }

    /** @test */
    public function get_name_excel_file()
    {
        $trait = new class { use StoragePath;};

        $this->assertEquals(
            $trait->getNameExcelFile(),
            'excel/reports.xlsx'
        );
    }

    /** @test */
    public function get_url_for_excel()
    {
        $trait = new class { use StoragePath;};

        $this->assertEquals(
            $trait->getUrlForExcel('test'),
            env('APP_URL') . '/storage/test'
        );
    }

    /** @test */
    public function patn_to_video_not_video_url()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        /** @var $report Report */
        $report = $this->reportBuilder->setUser($user)->create();

        $trait = new class { use StoragePath;};

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("No url for video");

        $trait->pathToVideo($report);
    }

    /** @test */
    public function patn_to_video_not_video_file()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        /** @var $report Report */
        $report = $this->reportBuilder->setUser($user)->create();

        $trait = new class { use StoragePath;};

        $model = new Video();
        $model->report_id = $report->id;
        $model->url = env('APP_URL') . '/storage/some_url.avi';
        $model->name = "some_url.avi";
        $model->save();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("No file in given path [/app/storage/app/public/video/{$report->id}/some_url.avi]");

        $trait->pathToVideo($report);
    }
}
