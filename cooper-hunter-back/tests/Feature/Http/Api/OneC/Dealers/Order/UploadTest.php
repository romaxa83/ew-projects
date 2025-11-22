<?php

namespace Tests\Feature\Http\Api\OneC\Dealers\Order;

use App\Models\Orders\Dealer\Order;
use App\Services\Orders\Dealer\OrderService;
use Exception;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Mockery\MockInterface;
use Tests\Builders\Dealers\DealerBuilder;
use Tests\Builders\Orders\Dealer\OrderBuilder;
use Tests\TestCase;

class UploadTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    protected OrderBuilder $orderBuilder;
    protected DealerBuilder $dealerBuilder;

    public function setUp(): void
    {
        parent::setUp();
        $this->orderBuilder = resolve(OrderBuilder::class);
        $this->dealerBuilder = resolve(DealerBuilder::class);
    }

    /** @test */
    public function success_upload(): void
    {
        Storage::fake('s3');
        $this->loginAsModerator();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $this->assertNull($model->files);

        $data = [
            'file' => 'z/Do4uXyIQ==',
            'name' => 'test',
            'extension' => 'xml',
        ];

        $this->postJson(
            route('1c.dealer-order.upload', ['guid' => $model->guid]),
            ['files' => [$data]]
        )
            ->assertOk()
            ->assertJson([
                'data' => 'Done',
                'success' => true
            ]);

        $model->refresh();

        $this->assertCount(1, $model->files);
    }

    /** @test */
    public function success_upload_new_file(): void
    {
        Storage::fake('s3');
        $this->loginAsModerator();

        /** @var $model Order */
        $model = $this->orderBuilder->setData([
            'files' => [
                [
                    'name' => 'test',
                    'url' => 'test',
                ]
            ]
        ])->create();

        $this->assertCount(1, $model->files);

        $data = [
            'file' => 'z/Do4uXyIQ==',
            'name' => 'test',
            'extension' => 'xml',
        ];

        $data2 = [
            'file' => 'z/Do4uXyIQ==',
            'name' => 'test1',
            'extension' => 'xml',
        ];

        $this->postJson(
            route('1c.dealer-order.upload', ['guid' => $model->guid]),
            ['files' => [$data, $data2]]
        )
            ->assertOk()
            ->assertJson([
                'data' => 'Done',
                'success' => true
            ]);

        $model->refresh();

        $this->assertCount(2, $model->files);
    }

    /** @test */
    public function fail_not_found(): void
    {
        Storage::fake('s3');
        $this->loginAsModerator();

        /** @var $model Order */
        $model = $this->orderBuilder->create();
        $guid = '8687568658658';

        $this->assertNull($model->files);

        $data = [
            'file' => 'z/Do4uXyIQ==',
            'name' => 'test',
            'extension' => 'xml',
        ];

        $this->postJson(
            route('1c.dealer-order.upload', ['guid' => $guid]),
            ['files' => [$data]]
        )
            ->assertJson([
                'data' => __(
                    'exceptions.dealer.order.not found by guid',
                    ['guid' => $guid]
                ),
                'success' => false
            ]);

        $model->refresh();

        $this->assertNull($model->files);
    }

    /** @test */
    public function fail_something_wrong_to_service(): void
    {
        $this->loginAsModerator();

        /** @var $model Order */
        $model = $this->orderBuilder->create();

        $data = [
            'file' => 'z/Do4uXyIQ==',
            'name' => 'test',
            'extension' => 'xml',
        ];

        $this->mock(OrderService::class, function (MockInterface $mock) {
            $mock->shouldReceive("uploadFilesFromOnec")
                ->andThrows(Exception::class, "some exception message");
        });

        $this->postJson(
            route('1c.dealer-order.upload', ['guid' => $model->guid]),
            ['files' => [$data]]
        )
            ->assertStatus(500)
            ->assertJson([
                'data' => "some exception message",
                'success' => false
            ]);
    }
}
