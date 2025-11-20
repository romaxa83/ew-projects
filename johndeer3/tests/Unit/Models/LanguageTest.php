<?php

namespace Tests\Unit\Models;

use App\Models\Languages;
use Tests\TestCase;

class LanguageTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function check_is_default(): void
    {
        /** @var $model Languages */
        $model = Languages::query()->where('slug', Languages::DEFAULT)->first();

        $this->assertTrue($model->isDefault());
    }

    /** @test */
    public function check_is_not_default(): void
    {
        /** @var $model Languages */
        $model = Languages::query()->where('slug', 'ua')->first();

        $this->assertFalse($model->isDefault());
    }
}
