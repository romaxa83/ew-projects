<?php

namespace App\Services\Catalog\Products;

use App\Dto\Catalog\Products\ProductKeywordDto;
use App\Models\Catalog\Products\Product;
use App\Models\Catalog\Products\ProductKeyword;

class ProductKeywordService
{
    public function update(ProductKeyword $productKeyword, ProductKeywordDto $dto): ProductKeyword
    {
        return $this->store($productKeyword, $dto);
    }

    protected function store(ProductKeyword $productKeyword, ProductKeywordDto $dto): ProductKeyword
    {
        $this->fill($productKeyword, $dto);

        $productKeyword->save();

        return $productKeyword;
    }

    protected function fill(ProductKeyword $productKeyword, ProductKeywordDto $dto): void
    {
        $productKeyword->keyword = $dto->getKeyword();
        $productKeyword->product_id = $dto->getProductId();
    }

    public function modify(Product $product, array $keywords): Product
    {
        $product->keywords()->delete();

        $keywords = array_unique($keywords);

        foreach ($keywords as $keyword) {
            $product->keywords()->create(
                compact('keyword')
            );
        }

        return $product;
    }

    public function delete(ProductKeyword $productKeyword): bool
    {
        return $productKeyword->delete();
    }

    public function create(ProductKeywordDto $dto): ProductKeyword
    {
        return $this->store(new ProductKeyword(), $dto);
    }
}