<?php

namespace Tests\Unit\Service\Translations\Transfer;

use App\Services\Translations\TransferService;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builder\TranslationBuilder;
use Tests\TestCase;

class FromDbToFilesTest extends TestCase
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
        $groupTest = 'test';
        list($ru, $en, $ua) =  ['ru', 'en', 'ua'];
        $t_en = $this->translationBuilder->setAlias('button')
            ->setGroup($groupTest)->setLang($en)->create();
        $t_ru = $this->translationBuilder->setAlias('button')
            ->setGroup($groupTest)->setLang($ru)->create();
        $t_ua = $this->translationBuilder->setAlias('button')
            ->setGroup($groupTest)->setLang($ua)->create();

        $pathFileRU = resource_path("lang") . "/{$ru}/{$groupTest}.php";
        $pathFileEN = resource_path("lang") . "/{$en}/{$groupTest}.php";
        $pathFileUA = resource_path("lang") . "/{$ua}/{$groupTest}.php";

        $this->assertFalse(file_exists($pathFileRU));
        $this->assertFalse(file_exists($pathFileEN));
        $this->assertFalse(file_exists($pathFileUA));

        $service = app(TransferService::class);
        $service->useFile = [$groupTest];

        $service->fromDdToFiles();

        $this->assertTrue(file_exists($pathFileRU));
        $translation_ru = \Lang::getLoader()->load($ru, $groupTest);
        $this->assertEquals($translation_ru['button'], $t_ru->text);

        $this->assertTrue(file_exists($pathFileEN));
        $translation_en = \Lang::getLoader()->load($en, $groupTest);
        $this->assertEquals($translation_en['button'], $t_en->text);

        $this->assertTrue(file_exists($pathFileUA));
        $translation_ua = \Lang::getLoader()->load($ua, $groupTest);
        $this->assertEquals($translation_ua['button'], $t_ua->text);

        $fileSys->delete($pathFileRU);
        $fileSys->delete($pathFileEN);
        $fileSys->delete($pathFileUA);
    }

    /** @test */
    public function success_dept(): void
    {
        $fileSys = app(Filesystem::class);
        $groupTest = 'test';
        list($ru, $en) =  ['ru', 'en'];
        $t_en = $this->translationBuilder->setAlias('button.one')
            ->setGroup($groupTest)->setLang($en)->create();
        $t_ru = $this->translationBuilder->setAlias('button.one')
            ->setGroup($groupTest)->setLang($ru)->create();

        $pathFileRU = resource_path("lang") . "/{$ru}/{$groupTest}.php";
        $pathFileEN = resource_path("lang") . "/{$en}/{$groupTest}.php";

        $this->assertFalse(file_exists($pathFileRU));
        $this->assertFalse(file_exists($pathFileEN));

        $service = app(TransferService::class);
        $service->useFile = [$groupTest];

        $service->fromDdToFiles();

        $this->assertTrue(file_exists($pathFileRU));
        $translation_ru = \Lang::getLoader()->load($ru, $groupTest);
        $this->assertEquals($translation_ru['button']['one'], $t_ru->text);

        $this->assertTrue(file_exists($pathFileEN));
        $translation_en = \Lang::getLoader()->load($en, $groupTest);
        $this->assertEquals($translation_en['button']['one'], $t_en->text);

        $fileSys->delete($pathFileRU);
        $fileSys->delete($pathFileEN);
    }

    /** @test */
    public function success_not_file(): void
    {
        $groupTest = 'test';
        list($ru, $en) =  ['ru', 'en'];
        $t_en = $this->translationBuilder->setAlias('button.one')
            ->setGroup($groupTest)->setLang($en)->create();

        $pathFileEN = resource_path("lang") . "/{$en}/{$groupTest}.php";

        $this->assertFalse(file_exists($pathFileEN));

        $service = app(TransferService::class);
        $service->useFile = ['testtt'];

        $service->fromDdToFiles();

        $this->assertFalse(file_exists($pathFileEN));
    }

    /** @test */
    public function success_change_data(): void
    {
        $fileSys = app(Filesystem::class);
        $groupTest = 'test';

        $data = [
            "test_1" => "translates_1",
            "test_2" => "translates_2 :arg !"
        ];

        $output = "<?php\n\nreturn " . var_export($data, true).';'.\PHP_EOL;
        $pathFileRU = resource_path("lang") . "/ru/{$groupTest}.php";

        $fileSys->put($pathFileRU, $output);

        $this->assertTrue(file_exists($pathFileRU));

        $translation_ru = \Lang::getLoader()->load("ru", $groupTest);
        $this->assertEquals($translation_ru['test_1'], $data['test_1']);
        $this->assertEquals($translation_ru['test_2'], $data['test_2']);

        $t_1 = $this->translationBuilder->setAlias('test_1')
            ->setGroup($groupTest)->setLang("ru")->create();
        $t_2 = $this->translationBuilder->setAlias('test_2')
            ->setGroup($groupTest)->setLang("ru")->create();

        $service = app(TransferService::class);
        $service->useFile = [$groupTest];

        $service->fromDdToFiles();

        $translation_ru = \Lang::getLoader()->load("ru", $groupTest);
        $this->assertNotEquals($translation_ru['test_1'], $data['test_1']);
        $this->assertNotEquals($translation_ru['test_2'], $data['test_2']);

        $fileSys->delete($pathFileRU);
    }
}
