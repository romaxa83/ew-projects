<?php

namespace Tests\Unit\Models\Localization;

use App\Models\Localization\Translation;
use Tests\TestCase;

class TranslationTest extends TestCase
{
    /** @test */
    public function list_place()
    {
        $list = Translation::listPLace();

        $this->assertIsArray($list);
        $this->assertNotEmpty($list);
    }

    /** @test */
    public function list_place_check_success()
    {
        $this->assertTrue(Translation::checkPLace(Translation::PLACE_APP));
    }

    /** @test */
    public function list_place_check_fail()
    {
        $this->assertFalse(Translation::checkPLace('wrong'));
    }

    /** @test */
    public function list_place_asset_fail()
    {
        $place = 'wrong';
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(__('error.translations.not defined place', ['place' => $place]));

        Translation::assetPLace($place);
    }

    /** @doesNotPerformAssertions */
    public function test_list_place_asset_success()
    {
        Translation::assetPLace(Translation::PLACE_APP);
    }

}




