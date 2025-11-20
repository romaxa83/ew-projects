<?php

namespace Tests\Unit\Jobs\Import;

use App\Exports\IosLinkExport;
use App\Jobs\Import\IosLinkImportJob;
use App\Jobs\MailSendIosLinkJob;
use App\Models\Import\IosLinkImport;
use App\Notifications\SendIosLink;
use App\Resources\Swagger\Link;
use App\Services\Import\Parser\IosLinkParser;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Maatwebsite\Excel\Facades\Excel;
use Mockery\MockInterface;
use Tests\Builder\UserBuilder;
use Tests\TestCase;
use Illuminate\Support\Facades\Notification;

class IosLinkImportJobTest extends TestCase
{
    use DatabaseTransactions;

    protected $userBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->userBuilder = resolve(UserBuilder::class);
    }

    /** @test */
    public function success(): void
    {
        $path = '/ios_link.xls';
        $export = new IosLinkExport([
            ["Code", "Code Redemption Link"],
            ["M9MRKNYNKE4R", "https://apps.apple.com/redeem?code=M9MRKNYNKE4R&ctx=apps"],
            ["63YEFKJ74J6M", "https://apps.apple.com/redeem?code=63YEFKJ74J6M&ctx=apps"],
            ["HANTFR3L34N3", "https://apps.apple.com/redeem?code=HANTFR3L34N3&ctx=apps"],
        ]);

        Excel::store($export, $path);

        $this->assertTrue(\Storage::disk('local')->exists($path));

        $filePath = \Storage::disk('local')->path($path);

        $user = $this->userBuilder->create();

        $import = IosLinkImport::factory()->create(["user_id" => $user->id]);

        $this->assertTrue($import->isNew());

        $job = new IosLinkImportJob($filePath, $import);

        $this->assertEquals($job->getPath(), $filePath);
        $this->assertEquals(md5($job->getImport()), md5($import));

        $job->handle();

        $this->assertTrue($import->isDone());
        $this->assertEquals($import->message, "Created count ios-links - 3");
        $this->assertNull($import->error_data);

        $this->assertFalse(\Storage::disk('local')->exists($path));
    }
//
//    /** @test */
//    public function fail_import(): void
//    {
//        $this->mock(IosLinkParser::class, function(MockInterface $mock){
//            $mock->shouldReceive("getCollection")
//                ->andReturns(\Exception::class, "some exception message");
//        });
//
//        $path = '/ios_link.xls';
//        $export = new IosLinkExport([
//            ["Code", "Code Redemption Link"],
//            [99, "https://apps.apple.com/redeem?code=M9MRKNYNKE4R&ctx=apps"],
//        ]);
//
//        Excel::store($export, $path);
//
//        $this->assertTrue(\Storage::disk('local')->exists($path));
//
//        $filePath = \Storage::disk('local')->path($path);
//
//        $user = $this->userBuilder->create();
//
//        $import = IosLinkImport::factory()->create(["user_id" => $user->id]);
//
//        $job = new IosLinkImportJob($filePath, $import);
//
//        $job->handle();
//
//        dd($import->refresh());
//
//        $this->assertTrue($import->isDone());
//        $this->assertEquals($import->message, "Created count ios-links - 3");
//        $this->assertNull($import->error_data);
//
//        $this->assertFalse(\Storage::disk('local')->exists($path));
//    }
}


