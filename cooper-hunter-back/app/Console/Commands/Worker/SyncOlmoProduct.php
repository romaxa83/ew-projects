<?php

namespace App\Console\Commands\Worker;

use App\Enums\Catalog\Products\ProductOwnerType;
use App\Helpers\DbConnections;
use App\Models\Catalog\Brands\Brand;
use App\Models\Catalog\Categories\Category;
use App\Models\Catalog\Products\Product;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use function React\Promise\Stream\first;

class SyncOlmoProduct extends Command
{
    protected $signature = 'worker:sync-olmo-product';

    protected $description = 'Синхронизация товаров из OLMO';

    protected bool $enable;
    protected string $apiUrl;

    protected array $relations = [];

    public function __construct()
    {
        parent::__construct();

        $this->enable = config('olmo.sync_enable');
        $this->apiUrl = config('olmo.api_url');
    }

    public function handle(): int
    {
        try {
            if($this->enable){
                logger_info('SYNC OLMO');
                $this->sync();
            }

            return self::SUCCESS;
        } catch (\Throwable $e) {
            logger_info('SYNC OLMO ERROR', [
                'err' => $e->getMessage()
            ]);
        }

        return self::FAILURE;
    }

    protected function sync(): void
    {
        if(!Product::olmo()->exists()){
            $this->fetchAll();
        } else {
            $this->update();
        }
    }

    protected function update(): void
    {
        $date = CarbonImmutable::now();

        Product::upsert(
            $this->transformData(
                DbConnections::olmo()
                    ->table('catalog_products')
                    ->whereNotNull('guid')
                    ->whereBetween('updated_at', [
                        $date->subHours(6),
                        $date
                    ])
                    ->get()
            ),
            'guid'
        );

        $this->syncRelations();
    }

    public function fetchAll(): void
    {
        Product::insert(
            $this->transformData(
                DbConnections::olmo()
                    ->table('catalog_products')
                    ->whereNotNull('guid')
                    ->get()
            )
        );

        $this->syncRelations();
    }

    public function transformData(Collection $collection): array
    {
        $category = Category::query()->olmo()->first();
        $brand = Brand::query()->olmo()->first();
        $sort = Product::query()->select('sort')->latest('sort')->toBase()->first()->sort;

        $tmp = [];
        $collection->each(function($p, $i) use ($category, $brand, &$sort, &$tmp) {
            $sort++;
            $tmp[$i] = [
                'guid' => $p->guid,
                'slug' => $p->slug,
                'title' => $p->title,
                'title_metaphone' => $p->title_metaphone,
                'category_id' => $category->id,
                'brand_id' => $brand->id,
                'owner_type' => ProductOwnerType::OLMO,
                'sort' => $sort,
                'created_at' => CarbonImmutable::now(),
                'updated_at' => CarbonImmutable::now(),
                'olmo_additions' => arrayToJson([
                    'media' => $this->getMedia($p->id)
                ]),
            ];

            $this->relations[$p->guid] = $this->getRelationsGuid($p->id);
        });

        return $tmp;
    }

    protected function syncRelations(): void
    {
        foreach ($this->relations as $productGuid => $relationsGuid){
            if(!empty($relationsGuid)){
                $product = Product::query()->where('guid', $productGuid)->first();
                $relationsID = Product::query()
                    ->select('id')
                    ->simple()
                    ->whereIn('guid', $relationsGuid)
                    ->get()
                    ->pluck('id')
                    ->toArray()
                ;

                $product->relationProducts()->sync($relationsID);
            }
        }
    }

    protected function getMedia($productID): array
    {
        $media = [];
        DbConnections::olmo()
            ->table('media')
            ->where('model_id', $productID)
            ->where('model_type', 'product')
            ->get()
            ->each(function($m, $k) use(&$media) {
                $media[$k] = "{$this->apiUrl}/storage/{$m->id}/{$m->file_name}";
            })
        ;

        return $media;
    }

    protected function getRelationsGuid($productID): array
    {
        return DbConnections::olmo()
            ->table('catalog_product_relations_pivot')
            ->where('product_id', $productID)
            ->join(
                'catalog_products',
                'catalog_products.id',
                '=',
                'catalog_product_relations_pivot.relation_id'
            )
            ->select('catalog_products.guid')
            ->get()->pluck('guid')
            ->toArray();
    }
}
