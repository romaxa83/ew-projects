<?php

namespace App\Console\Commands\Import;

use App\Enums\Orders\Dealer\PaymentType;
use App\Models\About\Page;
use App\Models\About\PageTranslation;
use Illuminate\Console\Command;

class PaymentDescription extends Command
{
    protected $signature = 'import:payment-desc';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->setDesc();
    }

    private function setDesc()
    {
        foreach ($this->data() as $item){
            if(!Page::query()->where('slug', $item['slug'])->first()){
                $model = new Page();
                $model->slug = $item['slug'];
                $model->is_page = false;
                $model->save();
                foreach ($item['translations'] as $lang => $name) {
                    $t = new PageTranslation();
                    $t->row_id = $model->id;
                    $t->language = $lang;
                    $t->title = $name;
                    $t->description = $name;
                    $t->save();
                }
            }
        }
    }

    private function data()
    {
        return [
            [
                'slug' => PaymentType::CARD(),
                'translations' => [
                    'en' => 'Card description',
                    'es' => 'Card description',
                ]
            ],
            [
                'slug' => PaymentType::PAYPAL(),
                'translations' => [
                    'en' => 'Paypal description',
                    'es' => 'Paypal description',
                ]
            ],
            [
                'slug' => PaymentType::BANK(),
                'translations' => [
                    'en' => 'Wired transfer description',
                    'es' => 'Wired transfer description',
                ]
            ],
            [
                'slug' => PaymentType::CHECK(),
                'translations' => [
                    'en' => 'Check description',
                    'es' => 'Check description',
                ]
            ],
            [
                'slug' => PaymentType::FLOORING(),
                'translations' => [
                    'en' => 'Flooring description',
                    'es' => 'Flooring description',
                ]
            ],
        ];
    }
}
