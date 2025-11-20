<?php

namespace Tests\Unit\Traits;

use App\Traits\ApiOrderBy;
use Tests\TestCase;

class ApiOrderByTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function check_fill_order_by_as_one()
    {
        $trait = new class { use ApiOrderBy; };
        $trait->setOrderBySupport('id', 'status');

        $this->assertEmpty($trait->orderDataForQuery());

        $data = ['status'];

        $trait->checkAndFillOrderBy($data);

        $this->assertEquals($trait->orderDataForQuery(), [
            "status" => "desc"
        ]);
    }

    /** @test */
    public function check_fill_order_by_as_few()
    {
        $trait = new class { use ApiOrderBy; };
        $trait->setOrderBySupport('id', 'status');

        $this->assertEmpty($trait->orderDataForQuery());

        $data = ['id', 'status'];

        $trait->checkAndFillOrderBy($data);

        $this->assertEquals($trait->orderDataForQuery(), [
            "id" => "desc",
            "status" => "desc"
        ]);
    }

    /** @test */
    public function check_fill_order_by_not_support_field()
    {
        $trait = new class { use ApiOrderBy; };
        $trait->setOrderBySupport('id', 'status');

        $this->assertEmpty($trait->orderDataForQuery());

        $data = ['id', 'status', "created"];

        $trait->checkAndFillOrderBy($data);

        $this->assertEquals($trait->orderDataForQuery(), [
            "id" => "desc",
            "status" => "desc"
        ]);
    }

    /** @test */
    public function check_fill_order_by_type_first_asc()
    {
        $trait = new class { use ApiOrderBy; };
        $trait->setOrderBySupport('id', 'status');

        $this->assertEmpty($trait->orderDataForQuery());

        $data = ['id', 'status'];

        $trait->checkAndFillOrderBy($data);
        $trait->checkAndFillOrderByType(["asc", "desc"]);

        $this->assertEquals($trait->orderDataForQuery(), [
            "id" => "asc",
            "status" => "desc"
        ]);
    }

    /** @test */
    public function check_fill_order_by_type_first_desc()
    {
        $trait = new class { use ApiOrderBy; };
        $trait->setOrderBySupport('id', 'status');

        $this->assertEmpty($trait->orderDataForQuery());

        $data = ['id', 'status'];

        $trait->checkAndFillOrderBy($data);
        $trait->checkAndFillOrderByType(["desc", "asc"]);

        $this->assertEquals($trait->orderDataForQuery(), [
            "id" => "desc",
            "status" => "asc"
        ]);
    }

    /** @test */
    public function check_fill_order_by_type_only_one()
    {
        $trait = new class { use ApiOrderBy; };
        $trait->setOrderBySupport('id', 'status');

        $this->assertEmpty($trait->orderDataForQuery());

        $data = ['id', 'status'];

        $trait->checkAndFillOrderBy($data);
        $trait->checkAndFillOrderByType(["asc"]);

        $this->assertEquals($trait->orderDataForQuery(), [
            "id" => "asc",
            "status" => "desc"
        ]);
    }
}
