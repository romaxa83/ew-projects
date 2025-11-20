<?php

namespace Tests\Unit\Models\Notification;

use App\Models\Notification\FcmTemplate;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class FcmTemplateTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function check_current_language(): void
    {
        /** @var $templatePlanned FcmTemplate */
        $templatePlanned = FcmTemplate::query()->where('type', FcmTemplate::PLANNED)->first();

        list($ru, $en) = ['ru', 'en'];

        \App::setLocale($ru);
        $this->assertEquals($ru, $templatePlanned->current->lang);

        \App::setLocale($en);
        $templatePlanned->refresh();
        $this->assertEquals($en, $templatePlanned->current->lang);
    }
}

