<?php

namespace App\Console\Commands;

use App\DTO\Page\PageDto;
use App\Models\Page\Page;
use App\Models\Translate;
use App\Models\Version;
use App\Repositories\PageRepository;
use App\Repositories\TranslationRepository;
use App\Services\PageService;
use Illuminate\Console\Command;

class Dev extends Command
{
    protected $signature = 'cmd:dev';

    public function __construct(
        protected PageService $pageService,
        protected PageRepository $pageRepository
    )
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->copyDisclaimer();
        $this->addPageVersion();

    }

    public function addPageVersion()
    {
        $data = $this->pageRepository->getForHash();

        Version::getHash($data);

        Version::setVersion(Version::PAGE, Version::getHash($data));
        Version::setVersion(Version::IMPORT_EG, null);
    }

    public function copyDisclaimer()
    {
        if(!$this->pageRepository->existBy('alias', Page::ALIAS_DISCLAIMER)){
            $models = app(TranslationRepository::class)
                ->getByModel(Translate::TYPE_DISCLAIMER);

            $data['type'] = Page::ALIAS_DISCLAIMER;

            foreach ($models as $lang => $item){
                $data['translations'][] = [
                    "lang" => $lang,
                    "text" => $item,
                    "name" => "Disclaimer (__translate into {$lang})",
                ];
            }

            $this->pageService->create(PageDto::byArgs($data));

            $this->info("disclaimer copy");
        }
    }
}
