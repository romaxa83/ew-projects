<?php

namespace Tests\Feature\Http\Api\V1\Model;

use App\Exceptions\ErrorsCode;
use App\Models\Catalogs\Car\Brand;
use App\Models\Catalogs\Car\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Traits\Statuses;
use Tests\Traits\UserBuilder;

class ModelStoreTest extends TestCase
{
    use DatabaseTransactions;
    use UserBuilder;
    use Statuses;

    public function setUp(): void
    {
        parent::setUp();
        $this->passportInit();
        $this->armaAuth();
    }

    public function headers()
    {
        return [
            'Authorization' => 'Basic d2V6b20tYXBpOndlem9tLWFwaQ=='
        ];
    }

    /** @test */
    public function create()
    {
        $data = [
            [
                'modelId' => 'some model id',
                'modelName' => 'some model name',
                'brandId' => 'some brand id',
                'brandName' => 'some brand name',
            ]
        ];

        $this->assertNull(Brand::query()->where('name', data_get($data, '0.brandName'))->first());
        $this->assertNull(Model::query()->where('name', data_get($data, '0.modelName'))->first());

        $this->post(
            route('api.v1.store.brad-model'),
            $data,
            $this->headers()
        )
            ->assertOk()
            ->assertJson([
                "data" => [],
                "success" => true,
            ])
            ->assertJsonCount(0, 'data')
        ;

        $this->assertNotNull(Brand::query()->where('name', data_get($data, '0.brandName'))->first());
        $this->assertNotNull(Model::query()->where('name', data_get($data, '0.modelName'))->first());
    }

    /** @test */
    public function update_brand()
    {
        $brand = Brand::query()->first();
        $data = [
            [
                'modelId' => 'some model id',
                'modelName' => 'some model name',
                'brandId' => '3b5bb1d4-58f3-11ec-8277-4cd98fc26f14',
                'brandName' => $brand->name,
            ]
        ];

        $this->assertNotEquals(data_get($data, '0.brandId'), $brand->uuid);

        $this->post(
            route('api.v1.store.brad-model'),
            $data,
            $this->headers()
        )
            ->assertOk()
            ->assertJson([
                "data" => [],
                "success" => true,
            ])
            ->assertJsonCount(0, 'data')
        ;

        $brand->refresh();

        $this->assertEquals(data_get($data, '0.brandId'), $brand->uuid);
    }

    /** @test */
    public function update_model()
    {
        $brand = Brand::query()->first();
        $model = Model::query()->where('brand_id', $brand->id)->first();

        $this->assertNotNull($model);
        $data = [
            [
                'modelId' => '4b5bb1d4-58f3-11ec-8277-4cd98fc26f14',
                'modelName' => $model->name,
                'brandId' => '3b5bb1d4-58f3-11ec-8277-4cd98fc26f14',
                'brandName' => $brand->name,
            ]
        ];

        $this->assertNotEquals(data_get($data, '0.modelId'), $model->uuid);

        $this->post(
            route('api.v1.store.brad-model'),
            $data,
            $this->headers()
        )
            ->assertOk()
            ->assertJson([
                "data" => [],
                "success" => true,
            ])
            ->assertJsonCount(0, 'data')
        ;

        $model->refresh();

        $this->assertEquals(data_get($data, '0.modelId'), $model->uuid);
    }

    /** @test */
    public function brand_id_like_null()
    {
        $data = [
            [
                'modelId' => 'some model id',
                'modelName' => 'some model name',
                'brandId' => null,
                'brandName' => 'some brand name',
            ]
        ];

        $this->post(
            route('api.v1.store.brad-model'),
            $data,
            $this->headers() + ['Content-Language' => 'en']
        )
            ->assertJson([
                "data" => 'The 0.brandId field is required.',
                "success" => false,
            ])
        ;
    }

    /** @test */
    public function brand_name_like_null()
    {
        $data = [
            [
                'modelId' => 'some model id',
                'modelName' => 'some model name',
                'brandId' => 'some brand id',
                'brandName' => null,
            ]
        ];

        $this->post(
            route('api.v1.store.brad-model'),
            $data,
            $this->headers() + ['Content-Language' => 'en']
        )
            ->assertJson([
                "data" => 'The 0.brandName field is required.',
                "success" => false,
            ])
        ;
    }

    /** @test */
    public function model_name_like_null()
    {
        $data = [
            [
                'modelId' => 'some model id',
                'modelName' => null,
                'brandId' => 'some brand id',
                'brandName' => 'some brand name',
            ]
        ];

        $this->post(
            route('api.v1.store.brad-model'),
            $data,
            $this->headers() + ['Content-Language' => 'en']
        )
            ->assertJson([
                "data" => 'The 0.modelName field is required.',
                "success" => false,
            ])
        ;
    }

    /** @test */
    public function model_id_like_null()
    {
        $data = [
            [
                'modelId' => null,
                'modelName' => 'some model name',
                'brandId' => 'some brand id',
                'brandName' => 'some brand name',
            ]
        ];

        $this->post(
            route('api.v1.store.brad-model'),
            $data,
            $this->headers() + ['Content-Language' => 'en']
        )
            ->assertJson([
                "data" => 'The 0.modelId field is required.',
                "success" => false,
            ])
        ;
    }

    /** @test */
    public function wrong_auth_token()
    {
        $headers = $this->headers();
        $headers['Authorization'] = 'wrong_token';

        $this->post(
            route('api.v1.store.brad-model'),
            [],
            $headers
        )
            ->assertStatus(ErrorsCode::NOT_AUTH)
            ->assertJson([
                "data" => 'Bad authorization token',
                "success" => false,
            ])
        ;
    }

    /** @test */
    public function without_auth_token()
    {
        $this->post(
            route('api.v1.store.brad-model'),
            [],[]
        )
            ->assertStatus(ErrorsCode::NOT_AUTH)
            ->assertJson([
                "data" => 'Missing authorization header',
                "success" => false,
            ])
        ;
    }
}
