<?php

namespace Tests\Unit\ValueObject;

use App\ValueObjects\Uuid;
use Tests\TestCase;

class UuidTest extends TestCase
{
    /** @test */
    public function create_success()
    {
        $uuid = \Ramsey\Uuid\Uuid::uuid4()->toString();

        $model = new Uuid($uuid);

        $this->assertEquals($model->getValue(), $uuid);
        $this->assertTrue($model->equalsTo($uuid));
        $this->assertFalse($model->isEmpty());
    }

    /** @test */
    public function create_success_not_equals()
    {
        $uuid = \Ramsey\Uuid\Uuid::uuid4()->toString();
        $uuid1 = \Ramsey\Uuid\Uuid::uuid4()->toString();

        $model = new Uuid($uuid);

        $this->assertFalse($model->equalsTo($uuid1));
    }

    /** @test */
    public function success_generate()
    {
        $model = Uuid::create();

        $this->assertTrue($model instanceof Uuid);
        $this->assertFalse($model->isEmpty());
    }

    public function fail_create()
    {
        $this->expectException(\InvalidArgumentException::class);

        new Uuid('some_str');
    }
}
