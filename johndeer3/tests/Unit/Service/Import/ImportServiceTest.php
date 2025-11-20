<?php

namespace Tests\Unit\Service\Import;

use App\Services\Import\ImportService;
use Mockery\MockInterface;
use Tests\TestCase;

class ImportServiceTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function check_request_success(): void
    {
        $data = [
            "test" => 'test'
        ];

        $service = $this->mock(ImportService::class, function(MockInterface $mock) use ($data){
            $mock->shouldReceive("getData")
                ->andReturn($data);
        });

        $result = $service->getData('/api/demo/products');

        $this->assertEquals($data, $result);
    }
}


