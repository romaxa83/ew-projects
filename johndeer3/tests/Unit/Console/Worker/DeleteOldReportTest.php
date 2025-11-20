<?php

namespace Tests\Unit\Console\Worker;

use App\Models\Report\Report;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\Builder\Report\ReportBuilder;
use Tests\TestCase;

class DeleteOldReportTest extends TestCase
{
    use DatabaseTransactions;
    protected $reportBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->reportBuilder = resolve(ReportBuilder::class);
    }

    /** @test */
    public function success(): void
    {
        Storage::fake('public');

        $datePast = Carbon::now()->sub(25, 'month');
        // REPORT 1
        $report_1 = $this->reportBuilder->setTitle("Some_title_1")
            ->setCreatedAt($datePast)->create();
        $report_1_id = $report_1->id;

        // video
        Storage::disk('public')
            ->putFileAs("/video/{$report_1->id}", UploadedFile::fake()->create("video.mp4"), 'video.mp4');

        Storage::disk('public')
            ->putFileAs('/pdf-report', UploadedFile::fake()->create("{$report_1->title}.pdf"), "{$report_1->title}.pdf");

        Storage::disk('public')
            ->putFileAs("/report/{$report_1->id}", UploadedFile::fake()->create("image_1.png"), 'image_1.png');
        Storage::disk('public')
            ->putFileAs("/report/{$report_1->id}", UploadedFile::fake()->create("image_2.png"), 'image_2.png');

        Storage::disk('public')->assertExists("/report/{$report_1->id}/image_1.png");
        Storage::disk('public')->assertExists("/report/{$report_1->id}/image_2.png");
        Storage::disk('public')->assertExists("/video/{$report_1->id}/video.mp4");
        Storage::disk('public')->assertExists("/pdf-report/{$report_1->title}.pdf");

        // REPORT 2
        $report_2 = $this->reportBuilder->setTitle("Some_title_2")
            ->setCreatedAt($datePast)->create();
        $report_2_id = $report_2->id;

        // video
        Storage::disk('public')
            ->putFileAs("/video/{$report_2->id}", UploadedFile::fake()->create("video.mp4"), 'video.mp4');

        Storage::disk('public')
            ->putFileAs('/pdf-report', UploadedFile::fake()->create("{$report_2->title}.pdf"), "{$report_2->title}.pdf");

        Storage::disk('public')
            ->putFileAs("/report/{$report_2->id}", UploadedFile::fake()->create("image_1.png"), 'image_1.png');
        Storage::disk('public')
            ->putFileAs("/report/{$report_2->id}", UploadedFile::fake()->create("image_2.png"), 'image_2.png');

        Storage::disk('public')->assertExists("/report/{$report_2->id}/image_1.png");
        Storage::disk('public')->assertExists("/report/{$report_2->id}/image_2.png");
        Storage::disk('public')->assertExists("/video/{$report_2->id}/video.mp4");
        Storage::disk('public')->assertExists("/pdf-report/{$report_2->title}.pdf");

        $this->artisan('jd:delete-report')
            ->assertExitCode(0);

        $this->assertNull(Report::query()->where('id', $report_1_id)->first());
        Storage::disk('public')->assertMissing("/report/{$report_1->id}/image_1.png");
        Storage::disk('public')->assertMissing("/report/{$report_1->id}/image_2.png");
        Storage::disk('public')->assertMissing("/video/{$report_1->id}/video.mp4");
        Storage::disk('public')->assertMissing("/pdf-report/{$report_1->title}.pdf");

        $this->assertNull(Report::query()->where('id', $report_2_id)->first());
        Storage::disk('public')->assertMissing("/report/{$report_2->id}/image_1.png");
        Storage::disk('public')->assertMissing("/report/{$report_2->id}/image_2.png");
        Storage::disk('public')->assertMissing("/video/{$report_2->id}/video.mp4");
        Storage::disk('public')->assertMissing("/pdf-report/{$report_2->title}.pdf");
    }

    /** @test */
    public function success_no_candidate(): void
    {
        $datePast = Carbon::now()->sub(23, 'month');
        // REPORT 1
        $report_1 = $this->reportBuilder->setTitle("Some_title_1")
            ->setCreatedAt($datePast)->create();

        // REPORT 2
        $report_2 = $this->reportBuilder->setTitle("Some_title_2")
            ->setCreatedAt($datePast)->create();

        $this->artisan('jd:delete-report')
            ->assertExitCode(0);

        $this->assertNotNull(Report::query()->where('id', $report_1->id)->first());
        $this->assertNotNull(Report::query()->where('id', $report_2->id)->first());
    }
}
