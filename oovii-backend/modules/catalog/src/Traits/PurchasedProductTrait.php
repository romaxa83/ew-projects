<?php

namespace WezomCms\Catalog\Traits;

use Carbon\Carbon;
use Lang;

trait PurchasedProductTrait
{
    /**
     * @return bool
     */
    public function availableForPurchase(): bool
    {
        return $this->available
            && $this->published
            && $this->amount > $this->minCountForPurchase()
            && (is_null($this->expires_at) || $this->expires_at >= Carbon::now()->startOfDay());
    }

    /**
     * @return string|null
     */
    public function unit(): ?string
    {
        return Lang::get('cms-catalog::' . app('side') . '.products.pieces');
    }

    /**
     * @return float|int
     */
    public function minCountForPurchase()
    {
        return 1;
    }

    /**
     * @return float|int
     */
    public function stepForPurchase(): float
    {
        return 1;
    }

    /**
     * @param  float  $quantity
     * @return bool
     */
    public function validatePurchaseQuantity(float $quantity): bool
    {
        return $quantity > 0
            && filter_var($quantity, FILTER_VALIDATE_INT) !== false
            && $this->amount >= $quantity
            && $this->amount_one_user >= $quantity;
    }

    /**
     * Product purchased price.
     *
     * @return float
     */
    public function priceForPurchase(): float
    {
        return $this->discounted()
            ? $this->cost_discount
            : $this->cost;
    }

    public function basePrice(): float
    {
        return $this->cost;
    }

    /**
     * Product old price.
     *
     * @return float|null
     */
    public function oldPriceForPurchase(): ?float
    {
        return $this->discounted() ? $this->cost : null;
    }

    /**
     * @param $count
     * @return bool
     */
    public function canDecreaseQuantity($count): bool
    {
        return $count - $this->stepForPurchase() >= $this->minCountForPurchase();
    }

    protected function discounted(): bool
    {
        return $this->cost_discount && $this->cost_discount < $this->cost;
    }
}
