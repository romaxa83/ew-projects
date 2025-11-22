<?php

namespace App\Entities\Warranty;

use App\Models\Catalog\Products\Product;

class WarrantyVerificationStatusEntity
{
    public bool $is_registered = false;
    public string $information;
    public ?string $purchase_date = null;
    public ?string $installation_date = null;
    public ?Product $product = null;

    public static function information(string $information): self
    {
        return (new self())->setInformation($information);
    }

    public function setInformation(string $information): self
    {
        $this->information = $information;

        return $this;
    }

    public function setRegistered(bool $registered): self
    {
        $this->is_registered = $registered;

        return $this;
    }

    public function setPurchaseDate(?string $purchaseDate): self
    {
        $this->purchase_date = $purchaseDate;

        return $this;
    }

    public function setInstallationDate(?string $installationDate): self
    {
        $this->installation_date = $installationDate;

        return $this;
    }

    public function setProduct(Product $product): self
    {
        $this->product = $product;

        return $this;
    }
}
