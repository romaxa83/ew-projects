<?php

namespace WezomCms\Imports\Jobs;

use DB;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Throwable;
use WezomCms\Catalog\Repositories\ProductRepository;
use WezomCms\Catalog\Services\ProductService;
use WezomCms\Imports\Jobs\Helpers\NewParser;
use WezomCms\Imports\Models\Import;
use WezomCms\Imports\Parsers\ProductParser;
use WezomCms\Imports\Services\ImportService;
use WezomCms\TelegramBot\Telegram;



class ProductImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $filePath;
    private Import $model;
    private ImportService $importService;
    private ProductService $productService;
    private ProductRepository $productRepo;

    public function __construct(string $filePath, Import $model)
    {
        $this->filePath = $filePath;
        $this->model = $model;
        $this->importService = app(ImportService::class);
        $this->productService = app(ProductService::class);
        $this->productRepo = app(ProductRepository::class);
    }

    /**
     * @throws Exception
     * @throws Throwable
     */
    public function handle(): void
    {
        $this->importService->setStatus(Import::STATUS_IN_PROCESS, $this->model);

//        $this->runParser();

        try {
            DB::beginTransaction();

            $data = app(NewParser::class, ['admin' => $this->model->administrator])->run($this->filePath);
            $count = Arr::get($data, 'countCreate');

            $this->importService->setDoneStatus(
                $this->model,
                "Upload [{$count}] product",
            );

            DB::commit();
        } catch (Throwable $e) {
            Telegram::error($e);
            DB::rollBack();

            $this->failed($e);

            throw $e;
        }
    }

    public function runParser()
    {
        $parser = new ProductParser($this->filePath);
        $parser->start();
        $data = $parser->getCollection();

        try {
            DB::beginTransaction();
            $count = 0;
            foreach ($data as $datum) {
                if($datum->attributes['parent']){
                    if($product = $this->productRepo->getOneBy('group_key', $datum->attributes['parent'], ['translations'])){
                        $datum->attributes['group_key'] = $datum->attributes['parent'];
                        $datum->attributes['publishedAt'] = $datum->attributes['publishedAt'] ?? $product->published_at;
                        $datum->attributes['expiresAt'] = $datum->attributes['expiresAt'] ?? $product->expires_at;
                        $datum->attributes['sort'] = $datum->attributes['sort'] ?? $product->sort;
                        $datum->attributes['moderator_id'] = $datum->attributes['moderator_id'] ?? $product->moderator_id;
                        $datum->attributes['provider_id'] = $datum->attributes['provider_id'] ?? $product->provider_id;
                        $datum->attributes['amountOneUser'] = $datum->attributes['amountOneUser'] ?? $product->amount_one_user;
                        foreach (array_keys(app('locales')) as $locale){
                            $datum->attributes['translations'][$locale]['name'] = $datum->attributes['translations'][$locale]['name']
                                ?? $product->translations->where('locale', $locale)->first()->name;
                            $datum->attributes['translations'][$locale]['description'] = $datum->attributes['translations'][$locale]['description']
                                ?? $product->translations->where('locale', $locale)->first()->description;
                            $datum->attributes['translations'][$locale]['feature_1'] = $datum->attributes['translations'][$locale]['feature_1']
                                ?? $product->translations->where('locale', $locale)->first()->feature_1;
                            $datum->attributes['translations'][$locale]['feature_2'] = $datum->attributes['translations'][$locale]['feature_2']
                                ?? $product->translations->where('locale', $locale)->first()->feature_2;
                            $datum->attributes['translations'][$locale]['feature_3'] = $datum->attributes['translations'][$locale]['feature_3']
                                ?? $product->translations->where('locale', $locale)->first()->feature_3;
                        }

                    }
                }

                $this->productService->createFromImport($datum->attributes);
                $count++;

            }
            DB::commit();
        } catch (Exception $e) {
            Telegram::error($e);
            DB::rollBack();

            throw $e;
        }

        $this->importService->setDoneStatus(
            $this->model,
            "Upload [{$count}] product"
        );
    }

    /**
     * @param Exception $exception
     */
    public function failed($exception): void
    {
        $this->model->update([
            'status' => Import::STATUS_FAILED,
            'message' => Str::substr($exception->getMessage(), 0, 180),
        ]);
    }
}
