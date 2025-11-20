<?php

namespace Tests\Unit\Models\User;

use App\Models\User\IosLink;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class IosLinkTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function success_create_from_import(): void
    {
        /** @var $model IosLink */
        $model = new IosLink();

        $data = [
            'code' => 'some_code',
            "link" => 'some_link',
        ];

        $this->assertNull(IosLink::query()->first());

        $model::createFromImport($data);

        $link = IosLink::query()->first();

        $this->assertEquals($link->link, data_get($data, 'link'));
        $this->assertEquals($link->code, data_get($data, 'code'));
    }

    /** @test */
    public function success_create_from_import_not_value(): void
    {
        /** @var $model IosLink */
        $model = new IosLink();

        $data = [
            'code' => null,
            "link" => null,
        ];

        $this->expectException(QueryException::class);

        $model::createFromImport($data);
    }

    /** @test */
    public function success_create_from_import_not_code(): void
    {
        /** @var $model IosLink */
        $model = new IosLink();

        $data = [
            "link" => null,
        ];

        $this->expectException(QueryException::class);

        $model::createFromImport($data);
    }

    /** @test */
    public function success_create_from_import_not_link(): void
    {
        /** @var $model IosLink */
        $model = new IosLink();

        $data = [
            'code' => null,
        ];

        $this->expectException(QueryException::class);

        $model::createFromImport($data);
    }
}

