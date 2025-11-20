<?php

namespace App\Console\Commands\Translates;

use App\Models\Translate;
use App\Models\User\Role;
use DB;
use Illuminate\Console\Command;
use Symfony\Component\Console\Helper\ProgressBar;

class TranslateRole extends Command
{
    protected $signature = 'jd:translates-role';

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

            $this->info('Translate role');
            $progressBar = new ProgressBar($this->output);
            $progressBar->setFormat('verbose');
            $progressBar->start();

            $countTran = 0;
            $countUpdate = 0;

            $roles = Role::query()->get();
            foreach ($roles as $key => $role){
                /** @var $role Role */
                foreach ($role->translate as $tran){
//dd($tran);
                    $model = Translate::query()
                        ->where('model', 'site')
                        ->where('alias', $role->role)
                        ->where('lang', $tran->lang)
                        ->first();

                    $countTran++;

                    if($model){
                        $tran->text = $model->text;
                        $tran->save();
                        $countUpdate++;
                        $progressBar->advance();
                    }
                }
            }

            $progressBar->finish();
            $this->info(PHP_EOL);
            $this->warn("Переводов {$countTran}");
            $this->warn("Обновлено {$countUpdate}");

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            dd($e->getMessage());
        }
    }
}

