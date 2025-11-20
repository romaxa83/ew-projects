<?php

namespace App\Console\Commands\Translates;

use App\Models\Translate;
use DB;
use Illuminate\Console\Command;
use Symfony\Component\Console\Helper\ProgressBar;

class CopyTranslates extends Command
{
    protected $signature = 'jd:copy-translates';

    protected $description = 'Copy translations';

    /**
     * @throws \Exception
     */
    public function handle()
    {
        $this->copyRowForAllLang();
    }

    public function copyRowForAllLang()
    {
        DB::beginTransaction();
        try {
            $defaultLang = Translate::defaultLang();
            $langs = Translate::getLanguage();
            $translations = Translate::query()->where('lang', $defaultLang)->get();


            $count = Translate::count();

            $this->info('Копируем англ. перевод для других языков (если для них не перевода)');
            $this->warn("Общее кол-во записей перед копированием - {$count}");
            $progressBar = new ProgressBar($this->output);
            $progressBar->setFormat('verbose');
            $progressBar->start();

            foreach ($translations as $key => $trans){

                /** @var $trans Translate */
                foreach ($langs as $lang => $name){
                    if($lang != $defaultLang){
                        if(!$this->checkExist($trans, $lang)){
                            $t = new Translate();
                            $t->model = $trans->model;
                            $t->entity_type = $trans->entity_type;
                            $t->entity_id = $trans->entity_id;
                            $t->text = $trans->text . " __(translate into {$name})";
                            $t->lang = $lang;
                            $t->alias = $trans->alias;
                            $t->group = $trans->group;
                            $t->save();

                            $progressBar->advance();
                        }
                    }
                }
            }

            $newCount = Translate::count();

            $progressBar->finish();
            $this->info(PHP_EOL);
            $this->warn("Общее кол-во записей после копирования копированием - {$newCount}");
            $add = $count - $newCount;
            $this->warn("Добавлено - {$add}");

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            dd($e->getMessage());
        }
    }

    private function checkExist(Translate $model, $lang): bool
    {
        $q = Translate::query()
            ->where('model', $model->model)
            ->where('alias', $model->alias)
            ->where('lang', $lang);

        if($model->entity_type){
            $q->where('entity_type', $model->entity_type);
        }
        if($model->entity_type){
            $q->where('entity_id', $model->entity_id);
        }
        if($model->group){
            $q->where('group', $model->group);
        }

        return $q->exists();
    }
}

