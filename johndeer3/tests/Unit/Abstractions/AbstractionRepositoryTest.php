<?php

namespace Tests\Unit\Abstractions;

use App\Models\User\Nationality;
use App\Repositories\NationalityRepository;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AbstractionRepositoryTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function check_get_all_by_limit_one()
    {
        $repo = app(NationalityRepository::class);

        $model = Nationality::query()->limit(1)->offset(5)->get();

        $data = $repo->getAllByLimit([], 1, 5);

        $this->assertEquals(md5($model), md5($data));
    }

    /** @test */
    public function check_get_all_by_limit_some()
    {
        $repo = app(NationalityRepository::class);

        $model = Nationality::query()->limit(5)->offset(3)->get();

        $data = $repo->getAllByLimit([], 5, 3);

        $this->assertEquals(md5($model), md5($data));
    }

    /** @test */
    public function check_exist_by_without_id()
    {
        $repo = app(NationalityRepository::class);

        $model = Nationality::query()->first();

        $data = $repo->existBy('id', $model->id);

        $this->assertTrue($data);

        $data = $repo->existBy('id', $model->id, $model->id);

        $this->assertFalse($data);
    }
}

