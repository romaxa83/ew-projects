<?php

namespace Tests\Feature\Api\Report\Files;

use App\Models\Image;
use App\Models\Report\Report;
use App\Models\User\Role;
use App\Models\User\User;
use App\Type\ReportStatus;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Builder\Feature\FeatureBuilder;
use Tests\Builder\UserBuilder;
use Tests\Feature\Api\Report\Create\CreateTest;
use Tests\TestCase;
use Tests\Traits\ResponseStructure;

class UpdateFilesTest extends TestCase
{
    use DatabaseTransactions;
    use ResponseStructure;

    protected $userBuilder;
    protected $featureBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
        $this->userBuilder = resolve(UserBuilder::class);
        $this->featureBuilder = resolve(FeatureBuilder::class);
    }

    /** @test */
    public function success_add_file()
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $data = CreateTest::data();

        Storage::fake('public');

        $file_1_name = Image::WORKING_START."_1.jpg";
        $file_2_name = Image::WORKING_START."_2.jpg";
        $file_3_name = Image::WORKING_START."_3.jpg";

        $data['files'] = [
            Image::WORKING_START => [
                UploadedFile::fake()->image($file_1_name),
            ],
        ];

        // create report with one file
        $res = $this->postJson(route('api.report.create'), $data);

        $id = data_get($res, 'data.id');
        $report = Report::query()->where('id', $id)->first();

        $this->assertCount(1, $report->images);
        $this->assertEquals($file_1_name, $report->images->first()->basename);

        $report->update(['status' => ReportStatus::OPEN_EDIT]);

        // data for update
        $data['files'] = [
            Image::WORKING_START => [
                $file_1_name,
                UploadedFile::fake()->image($file_2_name),
                UploadedFile::fake()->image($file_3_name),
            ],
        ];

        $this->postJson(route('api.report.update.ps', [
            'report' => $report
        ]), $data);

        $report->refresh();

        $this->assertCount(3, $report->images);
        $this->assertEquals($file_1_name, $report->images[0]->basename);
        $this->assertEquals($file_2_name, $report->images[1]->basename);
        $this->assertEquals($file_3_name, $report->images[2]->basename);

        $urlPart = "report/{$id}/";
        Storage::disk('public')->assertExists([
            $urlPart . $file_1_name,
            $urlPart . $file_2_name,
            $urlPart . $file_3_name,
        ]);
    }

    /** @test */
    public function success_one_add_and_one_delete()
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $data = CreateTest::data();

        Storage::fake('public');

        $file_1_name = Image::WORKING_START."_1.jpg";
        $file_2_name = Image::WORKING_START."_2.jpg";
        $file_3_name = Image::WORKING_START."_3.jpg";

        $data['files'] = [
            Image::WORKING_START => [
                UploadedFile::fake()->image($file_1_name),
                UploadedFile::fake()->image($file_2_name),
            ],
        ];

        // create report with one file
        $res = $this->postJson(route('api.report.create'), $data);

        $id = data_get($res, 'data.id');
        $report = Report::query()->where('id', $id)->first();
        $urlPart = "report/{$id}/";

        $this->assertCount(2, $report->images);
        $this->assertEquals($file_1_name, $report->images[0]->basename);
        $this->assertEquals($file_2_name, $report->images[1]->basename);

        Storage::disk('public')->assertExists([
            $urlPart . $file_1_name,
            $urlPart . $file_2_name,
        ]);

        $report->update(['status' => ReportStatus::OPEN_EDIT]);

        // data for update
        $data['files'] = [
            Image::WORKING_START => [
                $file_1_name,
                UploadedFile::fake()->image($file_3_name),
            ],
        ];

        $this->postJson(route('api.report.update.ps', [
            'report' => $report
        ]), $data);

        $report->refresh();

        $this->assertCount(2, $report->images);
        $this->assertEquals($file_1_name, $report->images[0]->basename);
        $this->assertEquals($file_3_name, $report->images[1]->basename);


        Storage::disk('public')->assertExists([
            $urlPart . $file_1_name,
            $urlPart . $file_3_name,
        ]);
    }

    /** @test */
    public function success_one_add_and_one_delete_some_modules()
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $data = CreateTest::data();

        Storage::fake('public');

        $file_1_name = Image::WORKING_START."_1.jpg";
        $file_2_name = Image::WORKING_START."_2.jpg";
        $file_3_name = Image::WORKING_START."_3.jpg";
        $file_4_name = Image::OTHERS."_4.jpg";
        $file_5_name = Image::OTHERS."_5.jpg";

        $data['files'] = [
            Image::WORKING_START => [
                UploadedFile::fake()->image($file_1_name),
                UploadedFile::fake()->image($file_2_name),
            ],
            Image::OTHERS => [
                UploadedFile::fake()->image($file_4_name),
            ],
        ];

        // create report with one file
        $res = $this->postJson(route('api.report.create'), $data);

        $id = data_get($res, 'data.id');
        $report = Report::query()->where('id', $id)->first();
        $urlPart = "report/{$id}/";

        $this->assertCount(3, $report->images);
        $this->assertEquals($file_1_name, $report->images[0]->basename);
        $this->assertEquals($file_2_name, $report->images[1]->basename);
        $this->assertEquals($file_4_name, $report->images[2]->basename);

        Storage::disk('public')->assertExists([
            $urlPart . $file_1_name,
            $urlPart . $file_2_name,
            $urlPart . $file_4_name,
        ]);

        $report->update(['status' => ReportStatus::OPEN_EDIT]);

        // data for update
        $data['files'] = [
            Image::WORKING_START => [
                $file_1_name,
                UploadedFile::fake()->image($file_3_name),
            ],
            Image::OTHERS => [
                UploadedFile::fake()->image($file_5_name),
            ],
        ];

        $this->postJson(route('api.report.update.ps', [
            'report' => $report
        ]), $data);

        $report->refresh();

        $this->assertCount(3, $report->images);
        $this->assertEquals($file_1_name, $report->images[0]->basename);
        $this->assertEquals($file_3_name, $report->images[1]->basename);
        $this->assertEquals($file_5_name, $report->images[2]->basename);


        Storage::disk('public')->assertExists([
            $urlPart . $file_1_name,
            $urlPart . $file_3_name,
            $urlPart . $file_5_name,
        ]);

        Storage::disk('public')->assertMissing([
            $urlPart . $file_4_name,
        ]);
    }

    /** @test */
    public function success_all_delete()
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $data = CreateTest::data();

        Storage::fake('public');

        $file_1_name = Image::WORKING_START."_1.jpg";
        $file_2_name = Image::WORKING_START."_2.jpg";

        $data['files'] = [
            Image::WORKING_START => [
                UploadedFile::fake()->image($file_1_name),
                UploadedFile::fake()->image($file_2_name),
            ],
        ];

        // create report with one file
        $res = $this->postJson(route('api.report.create'), $data);

        $id = data_get($res, 'data.id');
        $report = Report::query()->where('id', $id)->first();
        $urlPart = "report/{$id}/";

        $this->assertCount(2, $report->images);
        $this->assertEquals($file_1_name, $report->images[0]->basename);
        $this->assertEquals($file_2_name, $report->images[1]->basename);

        Storage::disk('public')->assertExists([
            $urlPart . $file_1_name,
            $urlPart . $file_2_name,
        ]);

        $report->update(['status' => ReportStatus::OPEN_EDIT]);

        // data for update
        $data['files'] = [
            Image::WORKING_START => [],
        ];

        $this->postJson(route('api.report.update.ps', [
            'report' => $report
        ]), $data);

        $report->refresh();

        $this->assertCount(0, $report->images);

        Storage::disk('public')->assertMissing([
            $urlPart . $file_1_name,
            $urlPart . $file_2_name,
        ]);
    }

    /** @test */
    public function success_not_change()
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $data = CreateTest::data();

        Storage::fake('public');

        $file_1_name = Image::WORKING_START."_1.jpg";
        $file_2_name = Image::WORKING_START."_2.jpg";

        $data['files'] = [
            Image::WORKING_START => [
                UploadedFile::fake()->image($file_1_name),
                UploadedFile::fake()->image($file_2_name),
            ],
        ];

        // create report with one file
        $res = $this->postJson(route('api.report.create'), $data);

        $id = data_get($res, 'data.id');
        $report = Report::query()->where('id', $id)->first();
        $urlPart = "report/{$id}/";

        $this->assertCount(2, $report->images);
        $this->assertEquals($file_1_name, $report->images[0]->basename);
        $this->assertEquals($file_2_name, $report->images[1]->basename);

        Storage::disk('public')->assertExists([
            $urlPart . $file_1_name,
            $urlPart . $file_2_name,
        ]);

        $report->update(['status' => ReportStatus::OPEN_EDIT]);

        // data for update
        $data['files'] = [
            Image::WORKING_START => [
                $file_1_name,
                $file_2_name,
            ],
        ];

        $this->postJson(route('api.report.update.ps', [
            'report' => $report
        ]), $data);

        $report->refresh();

        $this->assertCount(2, $report->images);
        $this->assertEquals($file_1_name, $report->images[0]->basename);
        $this->assertEquals($file_2_name, $report->images[1]->basename);

        Storage::disk('public')->assertExists([
            $urlPart . $file_1_name,
            $urlPart . $file_2_name,
        ]);
    }

    /** @test */
    public function success_add_signature()
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $data = CreateTest::data();

        Storage::fake('public');

        // create report with one file
        $res = $this->postJson(route('api.report.create'), $data);

        $id = data_get($res, 'data.id');
        $report = Report::query()->where('id', $id)->first();

        $this->assertCount(0, $report->images);

        $report->update(['status' => ReportStatus::OPEN_EDIT]);

        // data for update
        $data['files'] = [
            Image::SIGNATURE => [
                "[23,345,6,3,2,435,56]",
            ]
        ];

        $this->postJson(route('api.report.update.ps', [
            'report' => $report
        ]), $data);

        $report->refresh();

        $this->assertCount(1, $report->images);
        $this->assertEquals(Image::SIGNATURE.'.png', $report->images[0]->basename);

        Storage::disk('public')->assertExists([
            "report/{$id}/" . Image::SIGNATURE.'.png'
        ]);
    }

    /** @test */
    public function success_not_delete_if_exist()
    {
        /** @var $role Role */
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        $data = CreateTest::data();

        // data for update
        $data['files'] = [
            Image::SIGNATURE => [
                "[23,345,6,3,2,435,56]",
            ]
        ];

        Storage::fake('public');

        // create report with one file
        $res = $this->postJson(route('api.report.create'), $data);

        $id = data_get($res, 'data.id');
        $report = Report::query()->where('id', $id)->first();

        $this->assertCount(1, $report->images);
        $this->assertEquals(Image::SIGNATURE.'.png', $report->images[0]->basename);

        Storage::disk('public')->assertExists([
            "report/{$id}/" . Image::SIGNATURE.'.png'
        ]);

        $report->update(['status' => ReportStatus::OPEN_EDIT]);

        // data for update
        $data['files'] = [
            Image::SIGNATURE => []
        ];

        $this->postJson(route('api.report.update.ps', [
            'report' => $report
        ]), $data);

        $report->refresh();

        $this->assertCount(1, $report->images);
        $this->assertEquals(Image::SIGNATURE.'.png', $report->images[0]->basename);

        Storage::disk('public')->assertExists([
            "report/{$id}/" . Image::SIGNATURE.'.png'
        ]);
    }
}
