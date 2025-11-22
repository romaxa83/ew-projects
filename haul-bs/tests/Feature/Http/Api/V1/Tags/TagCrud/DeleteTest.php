<?php

namespace Tests\Feature\Http\Api\V1\Tags\TagCrud;

use App\Enums\Tags\TagType;
use App\Models\Tags\Tag;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Response;
use Tests\Builders\Customers\CustomerBuilder;
use Tests\Builders\Tags\TagBuilder;
use Tests\Builders\Vehicles\TrailerBuilder;
use Tests\Builders\Vehicles\TruckBuilder;
use Tests\TestCase;

class DeleteTest extends TestCase
{
    use DatabaseTransactions;

    protected TagBuilder $tagBuilder;
    protected CustomerBuilder $customerBuilder;
    protected TruckBuilder $truckBuilder;
    protected TrailerBuilder $trailerBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->tagBuilder = resolve(TagBuilder::class);
        $this->customerBuilder = resolve(CustomerBuilder::class);
        $this->truckBuilder = resolve(TruckBuilder::class);
        $this->trailerBuilder = resolve(TrailerBuilder::class);
    }

    /** @test */
    public function success_delete()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Tag */
        $model = $this->tagBuilder->create();

        $id = $model->id;

        $this->deleteJson(route('api.v1.tags.delete', ['id' => $model->id]))
            ->assertNoContent()
        ;

        $this->assertFalse(Tag::query()->where('id', $id)->exists());
    }

    /** @test */
    public function fail_used_customer()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Tag */
        $model = $this->tagBuilder->create();

        $this->customerBuilder->tags($model)->create();

        $res = $this->deleteJson(route('api.v1.tags.delete', ['id' => $model->id]))
        ;

        $link = str_replace('{id}', $model->id, config('routes.front.customers_with_tag_filter_url'));

        self::assertErrorMsg($res,__("exceptions.tag.used_customer", ['link' => $link]), Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /** @test */
    public function fail_has_truck_and_trailer()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Tag */
        $model = $this->tagBuilder->type(TagType::TRUCKS_AND_TRAILER())->create();

        $this->truckBuilder->tags($model)->create();
        $this->trailerBuilder->tags($model)->create();

        $res = $this->deleteJson(route('api.v1.tags.delete', ['id' => $model->id]))
        ;

        $truckLink = str_replace('{id}', $model->id, config('routes.front.trucks_with_tag_filter_url'));
        $trailerLink = str_replace('{id}', $model->id, config('routes.front.trailers_with_tag_filter_url'));

        self::assertErrorMsg($res, __("exceptions.tag.has_truck_and_trailer", [
            'trucks' => $truckLink,
            'trailers' => $trailerLink
        ]), Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /** @test */
    public function fail_has_truck()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Tag */
        $model = $this->tagBuilder->type(TagType::TRUCKS_AND_TRAILER())->create();

        $this->truckBuilder->tags($model)->create();

        $res = $this->deleteJson(route('api.v1.tags.delete', ['id' => $model->id]))
        ;

        $truckLink = str_replace('{id}', $model->id, config('routes.front.trucks_with_tag_filter_url'));

        self::assertErrorMsg($res, __("exceptions.tag.has_truck", [
            'trucks' => $truckLink,
        ]), Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /** @test */
    public function fail_has_trailer()
    {
        $this->loginUserAsSuperAdmin();

        /** @var $model Tag */
        $model = $this->tagBuilder->type(TagType::TRUCKS_AND_TRAILER())->create();

        $this->trailerBuilder->tags($model)->create();

        $res = $this->deleteJson(route('api.v1.tags.delete', ['id' => $model->id]))
        ;

        $trailerLink = str_replace('{id}', $model->id, config('routes.front.trailers_with_tag_filter_url'));

        self::assertErrorMsg($res, __("exceptions.tag.has_trailer", [
            'trailers' => $trailerLink
        ]), Response::HTTP_UNPROCESSABLE_ENTITY);
    }


    /** @test */
    public function fail_not_found()
    {
        $this->loginUserAsSuperAdmin();

        $res = $this->deleteJson(route('api.v1.tags.delete', ['id' => 0]));

        self::assertErrorMsg($res, __("exceptions.tag.not_found"), Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function not_perm()
    {
        $this->loginUserAsMechanic();

        /** @var $model Tag */
        $model = $this->tagBuilder->create();

        $res = $this->deleteJson(route('api.v1.tags.delete', ['id' => $model->id]));

        self::assertForbiddenMessage($res);
    }

    /** @test */
    public function not_auth()
    {
        /** @var $model Tag */
        $model = $this->tagBuilder->create();

        $res = $this->deleteJson(route('api.v1.tags.delete', ['id' => $model->id]));

        self::assertUnauthenticatedMessage($res);
    }
}
