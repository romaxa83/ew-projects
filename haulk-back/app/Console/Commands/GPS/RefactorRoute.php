<?php

namespace App\Console\Commands\GPS;

use App\Models\GPS\Route;
use Illuminate\Console\Command;

class RefactorRoute extends Command
{
    protected $signature = 'gps:refactor_route';

    public function handle(): int
    {
        $id = $this->ask('Enter route id');
        try {
            $start = microtime(true);

            $route = Route::find($id);

            $this->exec($route);

            $time = microtime(true) - $start;

        } catch (\Throwable $e) {
            $this->error($e->getMessage());
            return self::FAILURE;
        }

        $this->info("Done [time = {$time}]");
        return self::SUCCESS;
    }

    private function exec($route)
    {
        $tmp = $route->data;
        $prevKey = null;

        foreach ($route->data as $key => $items) {
            if($prevKey){
                if(isset($tmp[$key-1][$prevKey])){
                    $prev = $tmp[$key-1][$prevKey];
                    if(
                        last($prev) != current($items[array_key_first($items)])
                    ){
                        $tmp[$key-1][$prevKey][] = current($items[array_key_first($items)]);
                    }
                }
            }
            $prevKey = array_key_first($items);
        }

        $route->data = $tmp;
        $route->save();
    }
}
