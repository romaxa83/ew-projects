<?php

namespace Tests\Unit\Service\Translations\Transfer;

use App\Models\Translate;
use App\Services\Translations\TransferService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builder\TranslationBuilder;
use Tests\TestCase;

class CopyRowForAllLangTest extends TestCase
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
        $tran_en = $this->translationBuilder->setAlias('button')->setLang('en')->create();
        $this->translationBuilder->setAlias('exit')->setLang('en')->create();

        $this->assertEquals(1, Translate::query()->where('alias', 'button')->count());
        $this->assertEquals(1, Translate::query()->where('alias', 'exit')->count());

        $service = app(TransferService::class);

        $c = $service->copyRowForAllLang();

        $langs = Translate::getLanguage();
        $count = count($langs);

        $this->assertEquals($count, Translate::query()->where('alias', 'button')->count());
        $this->assertEquals($count, Translate::query()->where('alias', 'exit')->count());
        $this->assertEquals(($count * 2) - 2 , $c);

        $trans = Translate::query()->where('alias', 'button')->get();
        foreach ($langs as $lang => $name) {
            $m = $trans->where('lang', $lang)->first();
            if($lang == 'en'){
                $this->assertEquals($tran_en->text, $m->text);
            } else {
                $this->assertEquals($tran_en->text . " __(translate into {$name})", $m->text);
            }
            $this->assertEquals($tran_en->model, $m->model);
            $this->assertEquals($tran_en->alias, $m->alias);
            $this->assertEquals($tran_en->group, $m->group);
            $this->assertEquals($tran_en->entity_type, $m->entity_type);
            $this->assertEquals($tran_en->entity_id, $m->entity_id);
            $this->assertEquals($lang, $m->lang);
        }
    }

    /** @test */
    public function success_if_not_default_lang(): void
    {
        $this->translationBuilder->setAlias('button')->setLang('en')->create();
        $this->translationBuilder->setAlias('exit')->setLang('ru')->create();

        $this->assertEquals(1, Translate::query()->where('alias', 'button')->count());
        $this->assertEquals(1, Translate::query()->where('alias', 'exit')->count());

        $service = app(TransferService::class);

        $c = $service->copyRowForAllLang();

        $count = count($langs = Translate::getLanguage());

        $this->assertEquals($count, Translate::query()->where('alias', 'button')->count());
        $this->assertEquals(1, Translate::query()->where('alias', 'exit')->count());
        $this->assertEquals($count - 1 , $c);
    }

    /** @test */
    public function success_not_change_another_language(): void
    {
        $tran_en = $this->translationBuilder->setAlias('button')->setLang('en')->create();
        $tran_ru = $this->translationBuilder->setAlias('button')->setLang('ru')->create();
        $tran_ua = $this->translationBuilder->setAlias('button')->setLang('ua')->create();


        $this->assertEquals(3, Translate::query()->where('alias', 'button')->count());

        $service = app(TransferService::class);

        $c = $service->copyRowForAllLang();

        $langs = Translate::getLanguage();
        $count = count($langs);

        $this->assertEquals($count, Translate::query()->where('alias', 'button')->count());
        $this->assertEquals($count - 3 , $c);

        $trans = Translate::query()->where('alias', 'button')->get();
        foreach ($langs as $lang => $name) {
            $m = $trans->where('lang', $lang)->first();
            if($lang == 'en'){
                $this->assertEquals($tran_en->text, $m->text);
            } elseif ($lang == 'ru'){
                $this->assertEquals($tran_ru->text, $m->text);
            } elseif ($lang == 'ua'){
                $this->assertEquals($tran_ua->text, $m->text);
            } else {
                $this->assertEquals($tran_en->text . " __(translate into {$name})", $m->text);
            }
            $this->assertEquals($tran_en->model, $m->model);
            $this->assertEquals($tran_en->alias, $m->alias);
            $this->assertEquals($tran_en->group, $m->group);
            $this->assertEquals($tran_en->entity_type, $m->entity_type);
            $this->assertEquals($tran_en->entity_id, $m->entity_id);
            $this->assertEquals($lang, $m->lang);
        }
    }
}

