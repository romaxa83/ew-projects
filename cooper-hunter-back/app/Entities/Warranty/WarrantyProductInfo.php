<?php

namespace App\Entities\Warranty;

use Carbon\Carbon;
use JsonException;
use Throwable;

class WarrantyProductInfo
{
    public const DATE_FORMAT = 'Y-m-d';
    public const CAST_FORMAT = 'm/d/Y';

    public string $purchase_date;
    public string $installation_date;
    public string $installer_license_number;
    public string $purchase_place;

    public static function make(array $arr): self
    {
        $self = new self();

        $self->purchase_date = $arr['purchase_date'];
        $self->installation_date = $arr['installation_date'];
        $self->installer_license_number = $arr['installer_license_number'];
        $self->purchase_place = $arr['purchase_place'];

        return $self;
    }

    public function getPurchaseDateAsFormat(string $format = self::CAST_FORMAT): string
    {
        try {
            return $this->makePurchaseDate()->format($format);
        } catch (Throwable) {
            return $this->purchase_date;
        }
    }

    private function makePurchaseDate(): Carbon|false
    {
        if(str_contains($this->purchase_date, 'T')){
            $this->purchase_date = stristr($this->purchase_date, 'T', true);
        }

        return Carbon::createFromFormat(self::DATE_FORMAT, $this->purchase_date);
    }

    public function getInstallationDateAsFormat(string $format = self::CAST_FORMAT): string
    {
        try {
            return $this->makeInstallationDate()->format($format);
        } catch (Throwable) {
            return $this->purchase_date;
        }
    }

    private function makeInstallationDate(): Carbon|false
    {
        if(str_contains($this->installation_date, 'T')){
            $this->installation_date = stristr($this->installation_date, 'T', true);
        }

        return Carbon::createFromFormat(self::DATE_FORMAT, $this->installation_date);
    }

    public function datesAsTimestamp(): self
    {
        try {
            $this->purchase_date = $this->makePurchaseDate()->getTimestamp();
        } catch (Throwable) {
            $this->purchase_date = 0;
        }

        try {
            $this->installation_date = $this->makeInstallationDate()->getTimestamp();
        } catch (Throwable) {
            $this->installation_date = 0;
        }

        return $this;
    }

    /** @throws JsonException */
    public function toJson($options = 0): string
    {
        return json_encode($this->jsonSerialize(), JSON_THROW_ON_ERROR | $options);
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
