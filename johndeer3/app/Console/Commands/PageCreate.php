<?php

namespace App\Console\Commands;

use App\Models\Page\Page;
use App\Models\Page\PageTranslation;
use App\Models\Translate;
use App\Repositories\PageRepository;
use Illuminate\Console\Command;

class PageCreate extends Command
{
    protected $signature = 'jd:create-page';

    protected $description = 'Create page';
    /**
     * @var PageRepository
     */
    private $pageRepository;

    public function __construct(PageRepository $pageRepository)
    {
        parent::__construct();
        $this->pageRepository = $pageRepository;
    }

    public function handle()
    {
        $this->createPage(Page::ALIAS_PRIVATE_POLICY);
        $this->createPage(Page::ALIAS_AGREEMENT);
    }

    private function createPage($alias)
    {
        if(null !== $this->pageRepository->getByAlias($alias)){
            $this->warn("Уже есть стр. {$alias}");
            return null;
        }

        \DB::beginTransaction();
        try {
            $this->info("Создаем стр. {$alias}");

            $model = new Page();
            $model->alias = $alias;
            $model->save();

            $langs = Translate::getLanguage();

            foreach ($langs as $lang => $name){
                $t = new PageTranslation();
                $t->lang = $lang;
                $t->name = "title {$alias} __{$lang}";
                $t->text = "text {$alias} __{$lang}";
                $t->page_id = $model->id;
                $t->save();
            }

            \DB::commit();
        } catch(\Exception $exception) {
            \DB::rollBack();
            dd($exception->getMessage());
        }
    }
}


