<?php

namespace WezomCms\ProductReviews\Dto;

use WezomCms\Users\Models\User;

class ReviewDto
{
    public int $userId;
    public string $name;
    public ?string $email;
    public int $productId;
    public ?int $parentId;
    public bool $like;
    public string $text;
    public int $rating;

    private function __construct()
    {}

    public static function byRequest(array $data, User $user): self
    {
        $self = new self();

        $self->userId = $user->id;
        $self->name = $user->name;
        $self->email = $user->email;
        $self->productId = $data['product_id'];
        $self->parentId = $data['parent_id'] ?? null;
        $self->like = $data['like'];
        $self->text = $data['text'];
        $self->rating = $data['rating'] ?? 0;

        return $self;
    }
}
