<?php

namespace Tests\Unit\Listeners;

use App\Events\UpdateSysTranslations;
use App\Listeners\UpdateLangResourceListeners;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Builder\TranslationBuilder;
use Tests\TestCase;

class UpdateLangResourceListenersTest extends TestCase
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
        $t_1 = $this->translationBuilder->setAlias('test::button')
            ->setLang('en')->setGroup('test')->create();
        $t_2 = $this->translationBuilder->setAlias('test::button')
            ->setLang('ua')->setGroup('test')->create();

        $event = new UpdateSysTranslations([
            $t_1->id, $t_2->id
        ]);
        $listener = app(UpdateLangResourceListeners::class);
        $listener->handle($event);

        $this->assertTrue(true);
    }
}

