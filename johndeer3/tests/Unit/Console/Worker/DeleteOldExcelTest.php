<?php

namespace Tests\Unit\Console\Worker;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DeleteOldExcelTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function success_add(): void
    {
        Storage::fake('public');

        Storage::disk('public')
            ->putFileAs('/excel', UploadedFile::fake()->create("one.xlsx"), 'one.xlsx');
        Storage::disk('public')
            ->putFileAs('/excel', UploadedFile::fake()->create("two.xlsx"), 'two.xlsx');

        Storage::disk('public')->assertExists("/excel/one.xlsx");
        Storage::disk('public')->assertExists("/excel/two.xlsx");

        $this->artisan('jd:delete-excel')
            ->assertExitCode(0);

        Storage::disk('public')->assertMissing("/excel/one.xlsx");
        Storage::disk('public')->assertMissing("/excel/two.xlsx");
    }
}

