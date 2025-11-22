<?php


namespace App\Imports\Catalog;


use App\Models\Catalog\Features\Metric;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class FeatureMetricsImport implements ToCollection
{
    private const PRODUCT_LINE = 'Product';

    public function collection(Collection $collection)
    {
        foreach ($collection as $index => $item) {
            if ($item[0] !== self::PRODUCT_LINE) {
                continue;
            }

            $metrics = $collection->get(++$index);
            break;
        }
        if (empty($metrics)) {
            return;
        }

        $save = [];
        $inDb = Metric::all()
            ->pluck('name')
            ->toArray();

        foreach ($metrics as $metric) {
            $metric = explode(',', $metric);
            foreach ($metric as $item) {
                $item = trim($item);
                if (empty($item) || in_array($item, $save) || in_array($item, $inDb)) {
                    continue;
                }

                $save[] = $item;
            }
        }

        Metric::insert(
            array_map(
                fn(string $item) => ['name' => $item],
                $save
            )
        );
    }
}
