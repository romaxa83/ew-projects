<?php

namespace App\Services\Catalog;

use App\Dto\Catalog\Products\ProductDto;
use App\Exceptions\Catalog\SerialNumberNotFoundException;
use App\Models\Catalog\Brands\Brand;
use App\Models\Catalog\Certificates\Certificate;
use App\Models\Catalog\Certificates\CertificateType;
use App\Models\Catalog\Features\Value;
use App\Models\Catalog\Products\Product;
use App\Models\Catalog\Products\ProductSerialNumber;
use App\Traits\Model\ToggleActive;
use Carbon\CarbonImmutable;
use Core\Traits\Auth\AuthGuardsTrait;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Rap2hpoutre\FastExcel\FastExcel;
use Rap2hpoutre\FastExcel\SheetCollection;
use Throwable;

class ProductService
{
    use ToggleActive;
    use AuthGuardsTrait;

    public function create(ProductDto $dto): Product
    {
        return $this->modifyProduct($dto, new Product());
    }

    private function modifyProduct(ProductDto $dto, Product $product): Product
    {
        if (($guid = $dto->getGuid()) && ($this->isApiModerator() || $this->isSuperAdmin())) {
            $product->guid = $guid;
        }

        if ($dto->shouldUpdateActive()) {
            $product->active = $dto->getActive();
        }

        if ($dto->shouldUpdateShowRebate()) {
            $product->show_rebate = $dto->getShowRebate();
        }

        if ($dto->shouldUpdateCategoryId()) {
            $product->category_id = $dto->getCategoryId();
        }

        $product->unit_type_id = $dto->getUnitTypeId();
        $product->unit_sub_type = $dto->unitSubType;

        if ($dto->shouldUpdateTitle()) {
            $product->title = $dto->getTitle();
            $product->title_metaphone = makeSearchSlug($dto->getTitle());
        }

        if ($dto->shouldUpdateSlug()) {
            $product->slug = $dto->getSlug();
        }

        if ($dto->shouldUpdateSeer()) {
            $product->seer = $dto->getSeer();
        }

        if(null === $product->brand){
            if($brand = Brand::query()->cooper()->first()){
                $product->brand_id = $brand->id;
            }
        }

        $product->save();

        if ($this->isAdmin()) {
            $product->relativeCategories()->sync($dto->getRelativeCategoryIds());
        }

        if ($dto->shouldUpdateTranslations()) {
            foreach ($dto->getTranslations() ?? [] as $translation) {
                $product->translations()
                    ->updateOrCreate(
                        [
                            'language' => $translation->getLanguage()
                        ],
                        [
                            'description' => $translation->getDescription(),
                            'seo_title' => $translation->getSeoTitle(),
                            'seo_description' => $translation->getSeoDescription(),
                            'seo_h1' => $translation->getSeoH1(),
                        ]
                    );
            }
        }

        $this->syncRelations($dto, $product);

        if ($dto->shouldUpdateCertificates()) {
            $this->syncCertificates($dto, $product);
        }

        return $product->refresh();
    }

    private function syncRelations(ProductDto $dto, Product $product): void
    {
        if ($dto->shouldUpdateVideoLinkIds()) {
            $product->videoLinks()->sync($dto->getVideoLinkIds());
        }

        if ($dto->shouldUpdateRelationProducts()) {
            $product->relationProducts()->sync($dto->getRelationProducts());
        }

        if ($dto->shouldUpdateTroubleshootGroupIds()) {
            $product->troubleshootGroups()->sync($dto->getTroubleshootGroupIds());
        }

        if ($dto->shouldUpdateSpecificationIds()) {
            $product->specifications()->sync($dto->getSpecificationIds());
        }

        if ($dto->shouldUpdateManualIds()) {
            $product->manuals()->sync($dto->getManualIds());
        }

        if ($dto->shouldUpdateLabelIds()) {
            $product->labels()->sync($dto->getLabelIds());
        }

        if ($dto->shouldUpdateFeatureValues()) {
            $product->values()->sync($dto->getFeatureValues());
        }
    }

    private function syncCertificates(ProductDto $dto, Product $product): void
    {
        $certificates = $dto->getCertificates();

        $types = [];

        foreach ($certificates as $cert) {
            //put type as unique
            $types[$cert->getType()] = 1;
        }

        $types = array_keys($types);

        $certificateTypes = CertificateType::query()
            ->whereIn('type', $types)
            ->get()
            ->keyBy('type');

        if ($certificateTypes->count() !== count($types)) {
            CertificateType::query()
                ->insertOrIgnore(
                    collect($types)
                        ->diff(
                            $certificateTypes->pluck('type')
                        )->map(static fn($item) => ['type' => $item])
                        ->toArray()
                );

            $certificateTypes = CertificateType::query()
                ->whereIn('type', $types)
                ->get()
                ->keyBy('type');
        }

        $upsert = [];

        foreach ($certificates as $cert) {
            $upsert[] = [
                'certificate_type_id' => $certificateTypes->get($cert->getType())->id,
                'number' => $cert->getNumber(),
                'link' => $cert->getLink(),
            ];
        }

        Certificate::query()->upsert($upsert, ['certificate_type_id', 'number', 'link']);

        $ids = Certificate::query()
            ->whereIn('certificate_type_id', array_column($upsert, 'certificate_type_id'))
            ->whereIn('number', array_column($upsert, 'number'))
            ->whereIn('link', array_column($upsert, 'link'))
            ->pluck('id');

        $product->certificates()->sync($ids);
    }

    public function update(ProductDto $dto, Product $product): Product
    {
        return $this->modifyProduct($dto, $product);
    }

    /**
     * @param Product $model
     * @throws Exception
     */
    public function remove(Product $model): void
    {
        try {
            $model->forceDelete();
        } catch (Throwable $e) {
            logger($e->getMessage());
            throw new Exception($e->getMessage());
        }
    }

    public function searchProductBySerialNumber(string $serialNumber): Product
    {
        $serial = ProductSerialNumber::whereSerialNumber($serialNumber)
            ->first();

        if (!$serial) {
            throw new SerialNumberNotFoundException();
        }

        return $serial->product;
    }

    public function getSimilarProducts(Product $product): ?Collection
    {
        $product->refresh();
        /**@var Product[]|Collection $products */

        $features = $product->webValues->pluck('title', 'feature_id');

        if ($features->isEmpty()) {
            return null;
        }

        $values = $product
            ->category
            ->products()
            ->joinValues()
            ->select([Value::TABLE . '.feature_id', Value::TABLE . '.title'])
            ->whereIn('feature_id', $features->keys())
            ->groupBy(['feature_id', 'title'])
            ->orderBy('feature_id')
            ->orderBy('title')
            ->get();

        if ($values->isEmpty()) {
            return null;
        }

        $groupValues = [];

        foreach ($values as $value) {
            $groupValues[$value['feature_id']][] = $value['title'];
        }

        $availableValues = [];

        foreach ($features as $featureId => $featureValue) {
            if (!array_key_exists($featureId, $groupValues)) {
                continue;
            }
            $key = array_search($featureValue, $groupValues[$featureId]);

            if ($key === false) {
                continue;
            }

            $availableValues[$featureId] = [
                $groupValues[$featureId][$key]
            ];

            if (array_key_exists($key+1, $groupValues[$featureId])) {
                $availableValues[$featureId][] = $groupValues[$featureId][$key+1];
            }

            if (array_key_exists($key-1, $groupValues[$featureId])) {
                $availableValues[$featureId][] = $groupValues[$featureId][$key-1];
            }
        }
        unset($values, $groupValues, $features);

        if (empty($availableValues)) {
            return null;
        }

        $products = $product
            ->category
            ->products()
            ->where('id', '<>', $product->id)
            ->where(
                function (Builder $builder) use ($availableValues) {
                    foreach ($availableValues as $featureId => $values) {
                        $builder->orWhereHas(
                            'values',
                            fn (Builder $valuesBuilder) => $valuesBuilder->where('feature_id', $featureId)->whereIn('title', $values)
                        );
                    }
                }
            )
            ->get();

        if ($products->isEmpty()) {
            return null;
        }

        return $products;
    }

    public function generateExcelForDealerOrder(Collection $products): string
    {
        $basePath = storage_path('app/public/exports/order-dealer/');

        File::ensureDirectoryExists($basePath);

        $fileName = "products-". CarbonImmutable::now()->timestamp .".xlsx";
        $file = $basePath . $fileName;

        $data = [];
        foreach ($products as $item) {
            $data[] = [
                __('messages.file.id') => data_get($item, 'id'),
                __('messages.file.name') => data_get($item, 'title'),
                __('messages.file.brand') => data_get($item, 'brand'),
                __('messages.file.qty') => 0,
            ];
        }

        $sheets = new SheetCollection([
            'Products' => $data
        ]);

        (new FastExcel($sheets))->export($file);

        return url("/storage/exports/order-dealer/{$fileName}");
    }
}
