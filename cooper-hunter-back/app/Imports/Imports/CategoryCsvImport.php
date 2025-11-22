<?php

namespace App\Imports\Imports;

use App\Dto\Catalog\CategoryImportDto;
use App\Services\Catalog\Categories\CategoryService;
use Throwable;

class CategoryCsvImport
{
    public function __construct(private CategoryService $service)
    {
    }

    /**
     * @param string $pathToFile
     * @throws Throwable
     */
    public function run(string $pathToFile): void
    {
        makeTransaction(
            fn() => collect(file($pathToFile))
                ->map(
                    fn(string $item) => str_getcsv($item)
                )
                ->sortBy(0)
                ->each(
                    function (array $item)
                    {
                        if (empty($item[2])) {
                            return;
                        }

                        $this->service->createByImport(
                            CategoryImportDto::byArgs($item)
                        );
                    }
                )
        );
    }
}
