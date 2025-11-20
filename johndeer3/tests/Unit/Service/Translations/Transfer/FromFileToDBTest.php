<?php

namespace Tests\Unit\Service\Translations\Transfer;

use App\Models\Translate;
use App\Services\Translations\TransferService;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class FromFileToDBTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function success(): void
    {
        $data = [
            "test_1" => "translates_1",
            "test_2" => "translates_2 :arg !",
            "test_3" => [
                "test_3_1" => "translates_3_1",
                "test_3_2" => "translates_3_2 :arg !",
            ],
        ];
        $output = "<?php\n\nreturn " . var_export($data, true).';'.\PHP_EOL;
        $path = '/app/resources/lang/en/test.php';
        $fileSys = app(Filesystem::class);
        $fileSys->put($path, $output);

        $service = app(TransferService::class);
        $service->useFile = ["test"];

        $this->assertEmpty(Translate::query()->where('group', 'test')->get());

        $service->fromFilesToDB();

        $collection = Translate::query()->where('group', 'test')->get();

        $m_1 = $collection->where('alias', $service->makeKey('test', 'test_1'))->first();
        $this->assertNotNull($m_1);
        $this->assertEquals($m_1->model, Translate::TYPE_SITE);
        $this->assertEquals($m_1->lang, 'en');
        $this->assertEquals($m_1->text, $data['test_1']);

        $m_2 = $collection->where('alias', $service->makeKey('test', 'test_2'))->first();
        $this->assertNotNull($m_2);
        $this->assertEquals($m_2->model, Translate::TYPE_SITE);
        $this->assertEquals($m_2->lang, 'en');
        $this->assertEquals($m_2->text, $data['test_2']);

        $m_3 = $collection->where('alias', $service->makeKey('test', 'test_3.test_3_1'))->first();
        $this->assertNotNull($m_3);
        $this->assertEquals($m_3->model, Translate::TYPE_SITE);
        $this->assertEquals($m_3->lang, 'en');
        $this->assertEquals($m_3->text, $data['test_3']['test_3_1']);

        $m_4 = $collection->where('alias', $service->makeKey('test', 'test_3.test_3_2'))->first();
        $this->assertNotNull($m_4);
        $this->assertEquals($m_4->model, Translate::TYPE_SITE);
        $this->assertEquals($m_4->lang, 'en');
        $this->assertEquals($m_4->text, $data['test_3']['test_3_2']);

        $fileSys->delete($path);
    }

    /** @test */
    public function success_empty_file(): void
    {
        $data = [];
        $output = "<?php\n\nreturn " . var_export($data, true).';'.\PHP_EOL;
        $path = '/app/resources/lang/en/test.php';
        $fileSys = app(Filesystem::class);
        $fileSys->put($path, $output);

        $service = app(TransferService::class);
        $service->useFile = ["test"];

        $this->assertEmpty(Translate::query()->where('group', 'test')->get());

        $service->fromFilesToDB();

        $this->assertEmpty(Translate::query()->where('group', 'test')->get());

        $fileSys->delete($path);
    }

    /** @test */
    public function success_not_have_exclude(): void
    {
        $data = [];
        $output = "<?php\n\nreturn " . var_export($data, true).';'.\PHP_EOL;
        $path = '/app/resources/lang/en/test.php';
        $fileSys = app(Filesystem::class);
        $fileSys->put($path, $output);

        $service = app(TransferService::class);
        $service->useFile = ["tests"];

        $this->assertEmpty(Translate::query()->where('group', 'tests')->get());

        $service->fromFilesToDB();

        $this->assertEmpty(Translate::query()->where('group', 'tests')->get());

        $fileSys->delete($path);
    }
}
