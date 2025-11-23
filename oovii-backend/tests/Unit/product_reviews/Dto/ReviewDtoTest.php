<?php

namespace Tests\Unit\product_reviews\Dto;

use Tests\TestCase;
use WezomCms\ProductReviews\Dto\ReviewDto;
use WezomCms\Users\Models\User;

class ReviewDtoTest extends TestCase
{
    /** @test */
    public function fill_by_request_all_field(): void
    {
        $data = self::data();
        /** @var User $user */
        $user = User::factory()->create();

        $dto = ReviewDto::byRequest($data, $user);

        self::assertEquals($dto->userId, $user->id);
        self::assertEquals($dto->name, $user->name);
        self::assertEquals($dto->email, $user->email);
        self::assertEquals($dto->productId, array_get($data, 'product_id'));
        self::assertEquals($dto->parentId, array_get($data, 'parent_id'));
        self::assertEquals($dto->like, array_get($data, 'like'));
        self::assertEquals($dto->text, array_get($data, 'text'));
    }

    public static function data(): array
    {
        return [
            'product_id' => 1,
            'parent_id' => null,
            'like' => true,
            'text' => 'some review',
        ];
    }
}

