<?php

namespace App\Console\Commands\Warranty;

use App\Entities\Warranty\WarrantyProductInfo;
use App\Models\Warranty\WarrantyRegistration;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Throwable;

class WarrantyFixMissedDataCommand extends Command
{
    protected $signature = 'warranty:fix-registration-data';

    protected $description = 'Fix missed registration data.';

    public function handle(): int
    {
        $file = database_path('files/csv/osc_shop_warranty_cat_ref.csv');

        $data = fastexcel()
            ->withoutHeaders()
            ->import(
                $file,
                static fn(array $row) => [
                    'registration_id'          => $row[1],
                    'installation_date'        => $row[4],
                    'purchase_date'            => $row[5],
                    'purchase_place'           => $row[6],
                    'installer_licence_number' => $row[7],
                ],
            );

        $registrations = collect($data)
            ->keyBy('registration_id');

        $bar = $this->output->createProgressBar($registrations->count());

        $bar->start();

        foreach (WarrantyRegistration::query()->cursor() as $warranty) {
            if (
                is_null(
                    $registration = $registrations->get($warranty->id)
                )
            ) {
                $bar->advance();

                continue;
            }

            $productInfo = $this->fillNewProductInfo(
                $registration,
                $warranty->product_info
            );

            $warranty->product_info = $productInfo;
            $warranty->save();

            $bar->advance();
        }

        $bar->finish();

        return self::SUCCESS;
    }

    protected function fillNewProductInfo(
        array $registration,
        WarrantyProductInfo $productInfo
    ): WarrantyProductInfo {
        if (
            $installationDate = $this->safeDate(
                $registration['installation_date']
            )
        ) {
            $productInfo->installation_date = $installationDate;
        }

        if ($purchaseDate = $this->safeDate($registration['purchase_date'])) {
            $productInfo->purchase_date = trim($purchaseDate);
        }

        if ($purchasePlace = $registration['purchase_place'] ?? false) {
            $productInfo->purchase_place = trim($purchasePlace);
        }

        if ($license = $registration['installer_licence_number'] ?? false) {
            $productInfo->installer_license_number = trim($license);
        }

        return $productInfo;
    }

    protected function safeDate(string $date): string
    {
        $date = trim($date);

        $format = 'Y-m-d';

        try {
            return Carbon::createFromFormat($format, $date)->format($format);
        } catch (Throwable) {
            return '0000-00-00';
        }
    }
}
