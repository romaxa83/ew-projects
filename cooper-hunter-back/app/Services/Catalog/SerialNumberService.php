<?php

namespace App\Services\Catalog;

use App\Dto\Catalog\SerialNumberDto;
use App\Dto\OneC\ImportStatsDto;
use App\Models\Catalog\Products\Product;
use App\Models\Catalog\Products\ProductSerialNumber;

class SerialNumberService
{
    public function delete(Product $product, SerialNumberDto $dto): int
    {
        return $product->serialNumbers()->whereIn('serial_number', $dto->getSerialNumbers())->delete();
    }

    public function import(Product $product, SerialNumberDto $dto, bool $withStatistics = false): ImportStatsDto
    {
        if ($withStatistics) {
            return $this->insertSerialNumbers($product, $dto);
        }

        return $this->insertSerialNumbersWithoutStats($product, $dto);
    }

    protected function insertSerialNumbers(Product $product, SerialNumberDto $dto): ImportStatsDto
    {
        $upsert = [];
        $chunk = 1000;
        $i = 0;

        $exists = ProductSerialNumber::query()
            ->where('product_id', $product->id)
            ->whereIn('serial_number', $serialNumbers = $dto->getSerialNumbers())
            ->count();

        $updated = ProductSerialNumber::query()
            ->where('product_id', '<>', $product->id)
            ->whereIn('serial_number', $serialNumbers)
            ->count();

        foreach ($serialNumbers as $serialNumber) {
            $i++;

            $upsert[] = [
                'product_id' => $product->id,
                'serial_number' => $serialNumber
            ];

            if ($i === $chunk) {
                ProductSerialNumber::query()
                    ->upsert($upsert, ['serial_number']);

                $upsert = [];
                $i = 0;
            }
        }

        if (count($upsert)) {
            ProductSerialNumber::query()
                ->upsert($upsert, ['serial_number']);
        }

        $total = ProductSerialNumber::query()
            ->whereIn('serial_number', $serialNumbers)
            ->count();

        $new = $total - $exists - $updated;

        return ImportStatsDto::byArgs(
            compact('total', 'exists', 'new', 'updated')
        );
    }

    protected function insertSerialNumbersWithoutStats(Product $product, SerialNumberDto $dto): ImportStatsDto
    {
        $upsert = [];
        $chunk = 1000;
        $i = 0;

        $exists = 0;
        $updated = 0;

        foreach ($dto->getSerialNumbers() as $serialNumber) {
            $i++;

            $upsert[] = [
                'product_id' => $product->id,
                'serial_number' => $serialNumber
            ];

            if ($i === $chunk) {
                ProductSerialNumber::query()
                    ->upsert($upsert, ['serial_number']);

                $upsert = [];
                $i = 0;
            }
        }

        if (count($upsert)) {
            ProductSerialNumber::query()
                ->upsert($upsert, ['serial_number']);
        }

        $total = count($dto->getSerialNumbers());
        $new = 0;

        return ImportStatsDto::byArgs(
            compact('total', 'exists', 'new', 'updated')
        );
    }
}
