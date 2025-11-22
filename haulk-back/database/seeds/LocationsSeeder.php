<?php

use App\Services\ImportLocations\Worker\Import;
use Illuminate\Database\Seeder;

class LocationsSeeder extends Seeder
{
    /**
     * @throws Throwable
     */
    public function run(): void
    {
        try {
            DB::beginTransaction();
            $import = new Import();
            $import->parse();

            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            Log::info($exception->getTraceAsString());
        }
    }
}
