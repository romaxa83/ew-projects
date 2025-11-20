<?php

namespace Tests\Unit\Repository;

use App\Models\Languages;
use App\Repositories\LanguageRepository;
use Tests\TestCase;

class LanguageRepositoryTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function check_get_default(): void
    {
        $data = app(LanguageRepository::class)->getDefault();

        $this->assertEquals($data->slug, Languages::DEFAULT);
    }
}
