<?php

namespace App\Console\Commands\Helpers;

use App\Models\Catalog\Products\Product;
use App\Models\Companies\Company;
use App\Repositories\Companies\CompanyRepository;
use App\Services\Companies\CompanyService;
use Illuminate\Console\Command;
use Symfony\Component\Console\Helper\ProgressBar;

class AddPriceToCompany extends Command
{
    protected $signature = 'helpers:add-price';

    protected $description = 'Добавляет компании цен на все товары';

    public function __construct(
        protected CompanyRepository $repo,
        protected CompanyService $service,
    )
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->appPrice();

        return self::SUCCESS;
    }

    private function appPrice(): void
    {
        $id = $this->ask('Enter Company ID');

        $products = Product::get();
        $count = count($products);
        $progressBar = new ProgressBar($this->output, $count);

        try {
            /** @var $company Company */
            $company = $this->repo->getBy('id', $id, [], true);

            $progressBar->setFormat('verbose');
            $progressBar->start();

            foreach ($products as $product){
                $this->service->createOrUpdatePrice($company, $product, random_int(100, 10000));
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



