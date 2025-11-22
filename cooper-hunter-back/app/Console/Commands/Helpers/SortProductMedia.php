<?php

namespace App\Console\Commands\Helpers;

use App\Models\Catalog\Products\Product;
use App\Models\Media\Media;
use Illuminate\Console\Command;
use Symfony\Component\Console\Helper\ProgressBar;

class SortProductMedia extends Command
{
    protected $signature = 'helpers:sort-product-media';

    protected $description = 'Добавляет сортировку для медиа';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->sort();

        return self::SUCCESS;
    }

    private function sort(): void
    {
        $products = Product::query()->with('media')->get();
        $count = count($products);
        $progressBar = new ProgressBar($this->output, $count);

        try {
            $progressBar->setFormat('verbose');
            $progressBar->start();

            foreach ($products as $product){
                /** @var $product Product */
                foreach ($product->media as $k => $media){
                    /** @var $media Media */
                    $media->update(['sort' => $k]);
                }
                $progressBar->advance();
            }

            $progressBar->finish();
            echo PHP_EOL;

        } catch (\Exception $e){
            $progressBar->clear();
            $this->error($e->getMessage());
        }
    }
}
