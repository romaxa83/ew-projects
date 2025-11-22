<?php

namespace App\Dto\Catalog\Products;

class ProductKeywordDto
{
    private int $productId;
    private string $keyword;

    public static function byArgs(array $args): self
    {
        $dto = new self();

        $dto->productId = $args['product_id'];
        $dto->keyword = $args['keyword'];

        return $dto;
    }

    public function getProductId(): int
    {
        return $this->productId;
    }

    public function getKeyword(): string
    {
        return $this->keyword;
    }
}