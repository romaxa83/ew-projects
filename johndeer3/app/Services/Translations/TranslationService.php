<?php

namespace App\Services\Translations;

use App\DTO\Locale\TranslationDTO;
use App\DTO\Locale\TranslationsDTO;
use App\Events\UpdateSysTranslations;
use App\Models\Translate;
use App\Repositories\TranslationRepository;
use App\Services\Telegram\TelegramDev;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\Filesystem;

class TranslationService
{
    private $updateTranslateIDs = [];

    public function __construct(protected TranslationRepository $repo)
    {}

    public function create(TranslationDTO $dto): Translate
    {
        $model = new Translate();
        $model->model = $dto->model;
        $model->entity_type = $dto->entity_type;
        $model->entity_id = $dto->entity_id;
        $model->text = $dto->text;
        $model->lang = $dto->lang;
        $model->alias = $dto->alias;
        $model->group = $dto->group;

        $model->save();

        return $model;
    }

    public function update(Translate $model, TranslationDTO $dto): Translate
    {
        $model->text = $dto->text;
        $model->save();

        if($model->isSysAlias()){
            $this->updateTranslateIDs[] = $model->id;
        }

        return $model;
    }

    public function saveOrUpdate(TranslationsDTO $dto)
    {
        \DB::transaction(function () use ($dto) {
            foreach ($dto->getDtos() as $dto) {
                /** @var $dto TranslationDTO */
                /** @var $translate Translate */
                if($translate = $this->repo->getByAliasAndLang($dto->alias, $dto->lang)) {
                    $this->update($translate, $dto);
                } else {
                    $this->create($dto);
                }
            }
        });

        if(!empty($this->updateTranslateIDs)){
            event(new UpdateSysTranslations($this->updateTranslateIDs));
        }
    }

    // todo refactoring, по коду где используется save перевести на create
    public function save(
        $model,
        $entity,
        $entityId,
        $text,
        $lang,
        $alias = null
    ): Translate
    {
        $translate = new Translate();
        $translate->model = $model;
        $translate->entity_type = $entity;
        $translate->entity_id = $entityId;
        $translate->text = $text;
        $translate->lang = $lang;
        $translate->alias = $alias;

        $translate->save();

        return $translate;
    }

    public function saveOrUpdateForSiteFromArray(array $dataTranslates)
    {
        \DB::transaction(function () use ($dataTranslates) {
            foreach ($dataTranslates as $key => $data) {
                foreach ($data as $lang => $value) {
                    if($translate = $this->repo->getByAliasAndLang($key, $lang)) {
                        $translate->text = $value;
                        $translate->save();
                    } else {
                        $this->save(Translate::TYPE_SITE, null, null, $value, $lang, $key);
                    }
                }

            }
        });
    }

    public function exportToFile($key)
    {
        $group = current(explode('::', $key));

        if(Translate::checkExportGroup($group)){

            TelegramDev::info("Перезаписан в системных файлах перевод по ключу [{$key}]");

            $app = app(Application::class);
            $fileSystem = app(Filesystem::class);
            $basePath = $app['path.lang'];

            $tree = $this->makeTree(
                Translate::ofTranslatedGroup($group)
                    ->orderByGroupKeys(false)
                    ->get()
            );

            foreach ($tree as $lang => $groups){
                if(isset($groups[$group])){
                    $translations = $groups[$group];
                    $locale_path = $lang . DIRECTORY_SEPARATOR . $group;

                    $subfolders = explode(DIRECTORY_SEPARATOR, $locale_path);
                    array_pop($subfolders);
                    $subfolder_level = '';

                    foreach ($subfolders as $subfolder) {

                        $subfolder_level = $subfolder_level.$subfolder.DIRECTORY_SEPARATOR;

                        $temp_path = rtrim($basePath . DIRECTORY_SEPARATOR . $subfolder_level, DIRECTORY_SEPARATOR);
                        if (! is_dir($temp_path)) {
                            mkdir($temp_path, 0777, true);
                        }
                    }

                    $path = $basePath . DIRECTORY_SEPARATOR . $lang . DIRECTORY_SEPARATOR . $group . '.php';

                    $output = "<?php\n\nreturn " . var_export($translations, true).';'.\PHP_EOL;

                    $fileSystem->put($path, $output);
                }
            }
        }
    }

    protected function makeTree($translations)
    {
        $array = [];
        foreach ($translations as $translation) {
            $alias = last(explode('::', $translation->alias));
            \Arr::set($array[$translation->lang][$translation->group], $alias,
                $translation->text);
        }

        return $array;
    }
}
