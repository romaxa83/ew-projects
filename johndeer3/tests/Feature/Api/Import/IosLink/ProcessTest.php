<?php

namespace Tests\Feature\Api\Import\IosLink;

use App\Exports\IosLinkExport;
use App\Jobs\Import\IosLinkImportJob;
use App\Models\Import\IosLinkImport;
use App\Models\User\Role;
use App\Models\User\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Queue;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Illuminate\Http\Response;
use Tests\Traits\ResponseStructure;

class ProcessTest extends TestCase
{
    use DatabaseTransactions;
    use ResponseStructure;

    protected $userBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
        $this->userBuilder = resolve(UserBuilder::class);
    }

    /** @test */
    public function success()
    {
        $path = 'ios_link.xls';
        $export = new IosLinkExport([
            ["Code", "Code Redemption Link"],
            ["M9MRKNYNKE4R", "https://apps.apple.com/redeem?code=M9MRKNYNKE4R&ctx=apps"],
            ["63YEFKJ74J6M", "https://apps.apple.com/redeem?code=63YEFKJ74J6M&ctx=apps"],
            ["HANTFR3L34N3", "https://apps.apple.com/redeem?code=HANTFR3L34N3&ctx=apps"],
        ]);
        Excel::store($export, $path);
        //--------------------------------------
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        Storage::fake('public');
        Queue::fake();

        $data['file'] = UploadedFile::fake()->createWithContent("ios_link.xls", file_get_contents(Storage::path($path)));

        $this->assertNull(IosLinkImport::query()->first());

        $this->postJson(route('admin.ios-links.import.process'), $data)
            ->assertJson($this->structureSuccessResponse("File upload"))
        ;

        $model = IosLinkImport::query()->first();

        $this->assertTrue($model->isNew());
        $this->assertEquals($model->user_id, $user->id);
        $this->assertNotNull($model->file);
        $this->assertNull($model->message);
        $this->assertNull($model->error_data);

        Queue::assertPushed(IosLinkImportJob::class, function ($job) {
            return $job->getPath() == '/app/storage/app/import.xls'
                && $job->getImport() instanceof IosLinkImport;
        });

        unlink(Storage::path('public/' . $model->file));
    }

    /** @test */
    public function fail_exist_new_import()
    {
        $path = 'ios_link.xls';
        $export = new IosLinkExport([
            ["Code", "Code Redemption Link"],
            ["M9MRKNYNKE4R", "https://apps.apple.com/redeem?code=M9MRKNYNKE4R&ctx=apps"],
            ["63YEFKJ74J6M", "https://apps.apple.com/redeem?code=63YEFKJ74J6M&ctx=apps"],
            ["HANTFR3L34N3", "https://apps.apple.com/redeem?code=HANTFR3L34N3&ctx=apps"],
        ]);
        Excel::store($export, $path);
        //--------------------------------------
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        Storage::fake('public');

        IosLinkImport::factory()->create([
            "user_id" => $user->id,
            "status" => IosLinkImport::STATUS_NEW
        ]);

        $data['file'] = UploadedFile::fake()->createWithContent("ios_link.xls", file_get_contents(Storage::path($path)));

        $this->postJson(route('admin.ios-links.import.process'), $data)
            ->assertJson($this->structureErrorResponse("Import in process , try later"))
        ;
    }

    /** @test */
    public function fail_exist_in_process_import()
    {
        $path = 'ios_link.xls';
        $export = new IosLinkExport([
            ["Code", "Code Redemption Link"],
            ["M9MRKNYNKE4R", "https://apps.apple.com/redeem?code=M9MRKNYNKE4R&ctx=apps"],
            ["63YEFKJ74J6M", "https://apps.apple.com/redeem?code=63YEFKJ74J6M&ctx=apps"],
            ["HANTFR3L34N3", "https://apps.apple.com/redeem?code=HANTFR3L34N3&ctx=apps"],
        ]);
        Excel::store($export, $path);
        //--------------------------------------
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        Storage::fake('public');

        IosLinkImport::factory()->create([
            "user_id" => $user->id,
            "status" => IosLinkImport::STATUS_IN_PROCESS
        ]);

        $data['file'] = UploadedFile::fake()->createWithContent("ios_link.xls", file_get_contents(Storage::path($path)));

        $this->postJson(route('admin.ios-links.import.process'), $data)
            ->assertJson($this->structureErrorResponse("Import in process , try later"))
        ;
    }

    /** @test */
    public function fail_empty_file()
    {
        $path = 'ios_link.xls';
        $export = new IosLinkExport([]);
        Excel::store($export, $path);
        //--------------------------------------
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        Storage::fake('public');

        $data['file'] = UploadedFile::fake()->createWithContent("ios_link.xls", file_get_contents(Storage::path($path)));

        $this->postJson(route('admin.ios-links.import.process'), $data)
            ->assertJson($this->structureErrorResponse(["Invalid file data"]))
        ;
    }

    /** @test */
    public function fail_without_file()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        Storage::fake('public');

        $data['file'] = null;

        $this->postJson(route('admin.ios-links.import.process'), $data)
            ->assertJson($this->structureErrorResponse(["The file field is required."]))
        ;
    }

    /** @test */
    public function fail_wrong_extension()
    {
        /** @var $user User */
        $user = $this->userBuilder->create();
        $this->loginAsUser($user);

        Storage::fake('public');

        $data['file'] = UploadedFile::fake()->create("import.png");

        $this->postJson(route('admin.ios-links.import.process'), $data)
            ->assertJson($this->structureErrorResponse(["Invalid file extension"]))
        ;
    }

    /** @test */
    public function not_admin()
    {
        $role = Role::query()->where('role', Role::ROLE_PS)->first();
        /** @var $user User */
        $user = $this->userBuilder->setRole($role)->create();
        $this->loginAsUser($user);

        Storage::fake('public');

        $data['file'] = UploadedFile::fake()->create("import.xls");

        $this->postJson(route('admin.ios-links.import.process'), $data)
            ->assertStatus(Response::HTTP_FORBIDDEN)
            ->assertJson($this->structureErrorResponse(__('message.no_access')))
        ;
    }

    /** @test */
    public function not_auth()
    {
        Storage::fake('public');

        $data['file'] = UploadedFile::fake()->create("import.xls");

        $this->postJson(route('admin.ios-links.import.process'), $data)
            ->assertStatus(Response::HTTP_UNAUTHORIZED)
            ->assertJson($this->structureErrorResponse("Unauthenticated."))
        ;
    }
}


