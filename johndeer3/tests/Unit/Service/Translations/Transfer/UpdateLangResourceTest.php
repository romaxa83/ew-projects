<?php

namespace Tests\Unit\Service\Translations\Transfer;

use App\Repositories\TranslationRepository;
use App\Services\Translations\TransferService;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builder\TranslationBuilder;
use Tests\TestCase;

class UpdateLangResourceTest extends TestCase
{
    use DatabaseTransactions;

    protected $translationBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->translationBuilder = resolve(TranslationBuilder::class);
    }

    /** @test */
    public function success(): void
    {
        $fileSys = app(Filesystem::class);
        $data = [
            "test_1" => "translates_1",
            "test_2" => "translates_2 :arg !",
            "test_3" => [
                "test_3_1" => "translates_3_1",
                "test_3_2" => "translates_3_2 :arg !",
            ],
        ];
        $output = "<?php\n\nreturn " . var_export($data, true).';'.\PHP_EOL;

        $groupTest = 'test';
        list($en, $ua) =  ['en', 'ua'];

        $pathFileEN = resource_path("lang") . "/{$en}/{$groupTest}.php";
        $pathFileUA = resource_path("lang") . "/{$ua}/{$groupTest}.php";
        $fileSys->put($pathFileEN, $output);
        $fileSys->put($pathFileUA, $output);


        $t_1 = $this->translationBuilder->setGroup($groupTest)
            ->setAlias("{$groupTest}::test_3.test_3_1")->setLang($en)->create();
        $t_2 = $this->translationBuilder->setGroup($groupTest)
            ->setAlias("{$groupTest}::test_1")->setLang($ua)->create();

        $service = app(TransferService::class);
        $service->useFile = [$groupTest];

        $this->assertNotEquals(data_get($data, 'test_3.test_3_1'), $t_1->text);
        $this->assertNotEquals(data_get($data, 'test_1'), $t_2->text);

        $collection = app(TranslationRepository::class)->getObjByIDs([$t_1->id, $t_2->id]);
        $service->updateLangResource($collection);

        $data_en = \Lang::getLoader()->load($en, $groupTest);
        $data_ua = \Lang::getLoader()->load($ua, $groupTest);

        $this->assertEquals(data_get($data_en, 'test_1'), data_get($data, 'test_1'));
        $this->assertEquals(data_get($data_en, 'test_2'), data_get($data, 'test_2'));
        $this->assertEquals(data_get($data_en, 'test_3.test_3_1'), $t_1->text);
        $this->assertEquals(data_get($data_en, 'test_3.test_3_2'), data_get($data, 'test_3.test_3_2'));

        $this->assertEquals(data_get($data_ua, 'test_1'), $t_2->text);
        $this->assertEquals(data_get($data_ua, 'test_2'), data_get($data, 'test_2'));
        $this->assertEquals(data_get($data_ua, 'test_3.test_3_1'), data_get($data, 'test_3.test_3_1'));
        $this->assertEquals(data_get($data_ua, 'test_3.test_3_2'), data_get($data, 'test_3.test_3_2'));

        $fileSys->delete($pathFileEN);
        $fileSys->delete($pathFileUA);
    }

    /** @test */
    public function success_create_if_not_exist(): void
    {
        $fileSys = app(Filesystem::class);
        $data = [
            "test_2" => "translates_2 :arg !",
            "test_3" => [
                "test_3_1" => "translates_3_1",
                "test_3_2" => "translates_3_2 :arg !",
            ],
        ];
        $output = "<?php\n\nreturn " . var_export($data, true).';'.\PHP_EOL;

        $groupTest = 'test';
        list($en, $ua) =  ['en', 'ua'];

        $pathFileEN = resource_path("lang") . "/{$en}/{$groupTest}.php";
        $fileSys->put($pathFileEN, $output);


        $t_1 = $this->translationBuilder->setGroup($groupTest)
            ->setAlias("{$groupTest}::test_1")->setLang($en)->create();

        $service = app(TransferService::class);
        $service->useFile = [$groupTest];

        $this->assertNull(data_get($data, 'test_1'));

        $collection = app(TranslationRepository::class)->getObjByIDs([$t_1->id]);
        $service->updateLangResource($collection);

        $data_en = \Lang::getLoader()->load($en, $groupTest);

        $this->assertNotNull(data_get($data_en, 'test_1'));
        $this->assertEquals(data_get($data_en, 'test_1'), $t_1->text);

        $fileSys->delete($pathFileEN);
    }

    /** @test */
    public function success_not_file(): void
    {
        $groupTest = 'test';
        list($en, $ua) =  ['en', 'ua'];

        $pathFileEN = resource_path("lang") . "/{$en}/{$groupTest}.php";
        $pathFileUA = resource_path("lang") . "/{$ua}/{$groupTest}.php";

        $t_1 = $this->translationBuilder->setGroup($groupTest)
            ->setAlias("{$groupTest}::test_1")->setLang($ua)->create();

        $this->assertFalse(file_exists($pathFileUA));

        $service = app(TransferService::class);
        $service->useFile = [$groupTest];

        $collection = app(TranslationRepository::class)->getObjByIDs([$t_1->id]);
        $service->updateLangResource($collection);

        $this->assertFalse(file_exists($pathFileUA));
    }

    /** @test */
    public function success_not_use_group(): void
    {
        $fileSys = app(Filesystem::class);
        $data = [
            "test_2" => "translates_2 :arg !",
            "test_3" => [
                "test_3_1" => "translates_3_1",
                "test_3_2" => "translates_3_2 :arg !",
            ],
        ];
        $output = "<?php\n\nreturn " . var_export($data, true).';'.\PHP_EOL;

        $groupTest = 'test';
        list($en, $ua) =  ['en', 'ua'];

        $pathFileEN = resource_path("lang") . "/{$en}/{$groupTest}.php";
        $fileSys->put($pathFileEN, $output);

        $t_1 = $this->translationBuilder->setGroup("tet")
            ->setAlias("{$groupTest}::test_1")->setLang($en)->create();

        $service = app(TransferService::class);
        $service->useFile = [$groupTest];

        $this->assertNull(data_get($data, 'test_1'));

        $collection = app(TranslationRepository::class)->getObjByIDs([$t_1->id]);
        $service->updateLangResource($collection);

        $data_en = \Lang::getLoader()->load($en, $groupTest);

        $this->assertNull(data_get($data_en, 'test_1'));
        $this->assertFalse(file_exists(resource_path("lang") . "/{$en}/tet.php"));

        $fileSys->delete($pathFileEN);
    }
}
