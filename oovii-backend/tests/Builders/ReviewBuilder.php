<?php

namespace Tests\Builders;

use WezomCms\ProductReviews\Models\ProductReview;

class ReviewBuilder
{
    private string $text;
    private int $productId;
    private ?int $parent_id = null;
    private bool $admin_answer = false;

    private array $data = [
        'parent_id' => null,
        'admin_answer' => false,
    ];

    public function setText($value): self
    {
        $this->text = $value;
        $this->data['text'] = $value;

        return $this;
    }

    public function setProductId($value): self
    {
        $this->productId = $value;
        $this->data['product_id'] = $value;

        return $this;
    }

    public function setParentId($value): self
    {
        $this->parent_id = $value;
        $this->data['parent_id'] = $value;

        return $this;
    }

    public function setAdminAnswer($value): self
    {
        $this->admin_answer = $value;
        $this->data['admin_answer'] = $value;

        return $this;
    }

    public function create(): ProductReview
    {
        return $this->save();
    }

    private function save(): ProductReview
    {
        return ProductReview::factory()->new($this->data)->create();
    }
}
