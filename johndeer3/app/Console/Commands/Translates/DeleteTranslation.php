<?php

namespace App\Console\Commands\Translates;

use App\Models\Report\Feature\FeatureTranslation;
use App\Models\Report\Feature\FeatureValueTranslates;
use App\Models\Translate;
use Illuminate\Console\Command;
use Symfony\Component\Console\Helper\ProgressBar;

class DeleteTranslation extends Command
{
    protected $signature = 'jd:translates-delete';

    protected $description = 'Delete translation by alias';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @throws \Exception
     */
    public function handle()
    {
        $this->delete();
        $this->call('jd:export-translates');
    }

    private function delete()
    {
        $this->info('удаляем лишние переводы');
        $progressBar = new ProgressBar($this->output);
        $progressBar->setFormat('verbose');
        $progressBar->start();

        $deleteCount = 0;

        foreach ($this->aliases() as $alias){

            $trans = Translate::query()
                ->where('model', 'site')
                ->where('alias', $alias)
                ->get();

            foreach ($trans as $item){
                $item->delete();
                $deleteCount++;
                $progressBar->advance();
            }
        }



        $progressBar->finish();
        $this->info(PHP_EOL);
        $this->info("Deleted - {$deleteCount}");
    }

    private function aliases(): array
    {
        return [
            'validation::alpha',
            'validation::custom.attribute-name.rule-name',
            'validation::attributes.published_at',
            'validation::attributes.published_at',
            'validation::attributes.user_id',
            'validation::attributes.new_password_confirmation',
            'validation::attributes.ip',
            'validation::attributes.blocked_to',
            'validation::attributes.blocked_forever',
            'validation::attributes.commentable_id',
            'validation::attributes.identifier',
            'validation::attributes.publish_date',
            'validation::attributes.subject',
            'validation::attributes.seo_text',
            'validation::attributes.album_id',
            'validation::attributes.price',
            'validation::attributes.category',
            'validation::attributes.url',
            'validation::attributes.type',
            'validation::attributes.brand_id',
            'validation::attributes.available',
            'validation::attributes.old_price',
            'validation::attributes.color',
            'validation::attributes.birthday',
            'validation::attributes.sex',
            'validation::attributes.city',
            'validation::attributes.vendor_code',
            'validation::attributes.personal-data-processing',
            'validation::attributes.mark',
            'validation::attributes.answer',
            'validation::attributes.answered_at',
            'validation::attributes.microdata',
            'validation::attributes.delivery',
            'validation::attributes.payment_method',
            'validation::attributes.h1',
            'validation::attributes.title',
            'validation::attributes.keywords',
            'validation::attributes.description',
            'validation::attributes.per-page',
            'validation::attributes.roles-per-page',
            'validation::attributes.per-page-client-side',
            'validation::attributes.auth',
            'validation::attributes.history-per-page',
            'validation::attributes.autoplay',
            'validation::attributes.per-widget',
            'validation::attributes.count-in-widget',
            'validation::attributes.per-page-for-user',
            'validation::attributes.address_for_self_delivery',
            'validation::attributes.facebook-api-secret',
            'validation::attributes.facebook-api-key',
            'validation::attributes.twitter-api-key',
            'validation::attributes.twitter-api-secret',
            'validation::attributes.instagram-api-secret',
            'validation::attributes.instagram-api-key',
            'validation::attributes.login',
            'validation::attributes.user',
            'validation::attributes.slug',
            'validation::attributes.default',
            'validation::attributes.middle_name',
            'validation::attributes.created_at',
            'validation::attributes.updated_at',
            'validation::attributes.active',
            'validation::attributes.current_password',
            'validation::attributes.new_password',
            'validation::attributes.language',
            'validation::attributes.image',
            'validation::attributes.parent_id',
            'validation::attributes.date_from',
            'validation::attributes.date_to',
            'validation::attributes.short_content',
            'validation::attributes.comment',
            'validation::attributes.text',
            'validation::attributes.file',
            'validation::attributes.ru.name',
            'validation::attributes.ru.info',
            'validation::attributes.service_category_id',
            'validation::attributes.cost',
            'auth::password',
            'validation::attached',
            'validation::multiple_of',
            'validation::prohibited',
            'validation::prohibited_if',
            'validation::prohibited_unless',
            'validation::relatable',
            'validation::login',
            'validation::attributes.password_confirmation',
            'validation::attributes.month',
            'validation::attributes.gender',
            'validation::attributes.excerpt',
            'validation::ip',
            'validation::ipv4',
            'validation::ipv6',
            'validation::json',
            'test1',
        ];
    }
}



