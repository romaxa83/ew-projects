<?php

namespace Tests\_Helpers;

use App\Models\Catalogs\Calc\Work;
use Database\Factories\Catalogs\Calc\WorkFactory;
use Database\Factories\Catalogs\Calc\WorkTranslationFactory;

class CalcCatalogBuilder
{
    // work
    private $workMinutes = Work::DEFAULT_MINUTES;

    public function createWork(null|int $count = null): null|Work
    {
        if(null !== $count){
            Work::factory()->count($count)->create();

            return null;
        }

        $model = Work::factory()->create();

        WorkTranslationFactory::new(['model_id' => $model->id])->create(['lang' => 'ru']);
        WorkTranslationFactory::new(['model_id' => $model->id])->create(['lang' => 'uk']);

        return $model;
    }

    private function clear()
    {
        // work
        $this->workMinutes = Work::DEFAULT_MINUTES;
    }
}

