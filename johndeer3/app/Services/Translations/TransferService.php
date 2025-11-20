<?php

namespace App\Services\Translations;

use App\DTO\Locale\TranslationDTO;
use App\Models\Translate;
use App\Repositories\TranslationRepository;
use Arr;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;

/**
 * сервис позволяет импортировать переводы из файлов в бд, и обратно
 * из бд в файлы, для возможности редактировать переводы из админки
 */
class TransferService
{
    // Названия файлов (resources/lang/en), которые будут учавствовать в трансфере
    public $useFile = [
        'auth',
        'message',
        'passwords',
        'translates',
        'validation'
    ];

    // файлы какого языка будут взяты за основу
    protected $defaultLang = 'en';

    public function __construct(
        protected Application $app,
        protected  Filesystem $files,
        protected  TranslationRepository $repo,
        protected  TranslationService $service
    )
    {}

    public function makeKey($group, $alias): string
    {
        return $group .Translate::SEPARATOR_SYS_ALIAS. $alias;
    }

    /**
     * метод, из указанных в useFile названий файлов с переводами,
     * записывает их в бд, где в поле group - записывается название файла,
     * а в alias - название файла ."::". ключ перевода, если в бд перевод
     * есть (сравнивает по group, alias, lang), то не записывает данные перевод,
     * возвращает кол-во записаных переводов
    */
    public function fromFilesToDB(): int
    {
        $count = 0;
        $pathToLangFiles = $this->app['path.lang'];
        foreach ($this->files->directories($pathToLangFiles) as $langPathFile){
            $locale = basename($langPathFile); //en
            foreach ($this->files->allfiles($langPathFile) as $file){

                $info = pathinfo($file);
                $group = $info['filename'];

                if(!in_array($group, $this->useFile)) {
                    continue;
                }

                $subLangPath = str_replace($langPathFile.DIRECTORY_SEPARATOR, '', $info['dirname']);
                $subLangPath = str_replace(DIRECTORY_SEPARATOR, '/', $subLangPath);
                $langPathFile = str_replace(DIRECTORY_SEPARATOR, '/', $langPathFile);

                if ($subLangPath != $langPathFile) {
                    $group = $subLangPath.'/'.$group;
                }

                $translations = \Lang::getLoader()->load($locale, $group);

                if ($translations && is_array($translations)) {
                    foreach (Arr::dot($translations) as $key => $value) {
                        $key = $this->makeKey($group, $key);

                        if(!$this->repo->existByGroupAliasLang($group, $key, $locale)){
                            $this->service->create(TranslationDTO::byArgs([
                                "model" => Translate::TYPE_SITE,
                                "group" => $group,
                                "alias" => $key,
                                "text" => $value,
                                "lang" => $locale,
                            ]));
                            $count++;
                        }
                    }
                }
            }
        }

        return $count;
    }

    /**
     * в таблице перевод, копирует запись для всех языков приложения, для того чтоб
     * можно было добавить перевод в админке, если перевод по какому-то языку есть,
     * игнорирует данную запись
     * возвращает кол-во созданых записей в бд
     */
    public function copyRowForAllLang(): int
    {
        $count = 0;
        $langs = Translate::getLanguage();

        \DB::table(Translate::TABLE)
            ->where('lang', $this->defaultLang)
            ->orderBy('id')
            ->chunk(100, function ($models) use ($langs, &$count) {
                foreach ($models as $obj) {
                    foreach ($langs as $lang => $name) {
                        if($lang != $this->defaultLang){
                            if(!$this->repo->existForCopy($obj, $lang)){
                                $this->service->create(TranslationDTO::byArgs([
                                    "model" => $obj->model,
                                    "group" => $obj->group,
                                    "alias" => $obj->alias,
                                    "text" => $obj->text . " __(translate into {$name})",
                                    "lang" => $lang,
                                    "entity_type" => $obj->entity_type,
                                    "entity_id" => $obj->entity_id,
                                ]));
                                $count++;
                            }
                        }
                    }
                }
        });

        return $count;
    }

    /**
     * из бд, записи с group указанной в useFile, переносятся в файлы переводов,
     * соответственно создавая папки по локали, которые есть в бд,
     * возвращает информацию о записях
     */
    public function fromDdToFiles(): array
    {
        $basePath = $this->app['path.lang'];
        $info = [];
        foreach($this->useFile ?? [] as $group){
            $tree = $this->makeTree(
                Translate::ofTranslatedGroup($group)
                    ->orderByGroupKeys(false)
                    ->toBase()
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

                    $this->files->put($path, $output);

                    $info[$path] = count($translations);
                }
            }
        }

        return $info;
    }

    public function updateLangResource(Collection $collection)
    {
        try {
            foreach($this->useFile ?? [] as $group){
                $tree = $this->makeTree($collection);

                foreach ($tree as $lang => $groups){
                    if(isset($groups[$group])){
                        $translations = $groups[$group];
                        $file_path = resource_path("lang").DIRECTORY_SEPARATOR.$lang.DIRECTORY_SEPARATOR.$group.".php";

                        if(file_exists($file_path)){
                            $data = \Lang::getLoader()->load($lang, $group);

                            $tmp = array_merge(\Arr::dot($data), \Arr::dot($translations));
                            $output = "<?php\n\nreturn " . var_export(array_undot($tmp), true).';'.\PHP_EOL;
//                        dd($file_path, $output);

                            $this->files->put($file_path, $output);
//                            dd($d);
                        }

                    }
                }
            }
        } catch (\Exception $e) {
            dd($e->getMessage());
        }

    }

    protected function makeTree($translations)
    {
        $array = [];
        foreach ($translations as $translation) {
            $alias = last(explode(Translate::SEPARATOR_SYS_ALIAS, $translation->alias));
            Arr::set(
                $array[$translation->lang][$translation->group],
                $alias,
                $translation->text
            );
        }

        return $array;
    }
}
