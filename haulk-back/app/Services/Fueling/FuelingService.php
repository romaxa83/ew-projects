<?php

namespace App\Services\Fueling;

use App\Enums\Fueling\FuelingHistoryStatusEnum;
use App\Enums\Fueling\FuelingSourceEnum;
use App\Exceptions\HasRelatedEntitiesException;
use App\Imports\FuelingShortImport;
use App\Jobs\Fueling\ImportJob;
use App\Models\Fueling\Fueling;
use App\Models\Fueling\FuelingHistory;
use App\Services\Events\Fueling\FuelingHistoryEventService;
use Clockwork\Storage\Storage;
use DB;
use Exception;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;

class FuelingService
{
    public function import(array $args): FuelingHistory
    {
        /** @var UploadedFile $file */
        $file = $args['file'];

        $fileStorage = '/tmp/' . $file->getFileInfo()->getFilename();
        $file->storeAs($fileStorage, $file->getClientOriginalName());

        $import = new FuelingShortImport();

        Excel::import(
            $import,
            $fileStorage . '/' . $file->getClientOriginalName(),
            null,
            \Maatwebsite\Excel\Excel::CSV);

        $history = new FuelingHistory();
        $history->status = FuelingHistoryStatusEnum::IN_QUEUE;
        $history->progress = 0;
        $history->path_file = $fileStorage . '/' . $file->getClientOriginalName();
        $history->original_name = $file->getClientOriginalName();
        $history->provider = $args['provider'];
        $history->user_id = $args['user_id'];
        $history->total = $import->getRowCount();
        $history->save();

        $history->refresh();

        FuelingHistoryEventService::fuelingHistory($history)->user($history->user)->broadcast();

        ImportJob::dispatch($history)->onQueue('import');

        return $history;
    }

    /**
     * @throws HasRelatedEntitiesException
     */
    public function destroy(Fueling $fueling): Fueling
    {
        if ($fueling->hasRelatedEntities()) {
            throw new HasRelatedEntitiesException();
        }

        $fueling->delete();

        return $fueling;
    }

    public function update(Fueling $fueling, array $attributes)
    {
        try {
            DB::beginTransaction();

            $attributes['source'] = FuelingSourceEnum::MANUALLY;
            $attributes['amount'] = $attributes['unit_price'] * $attributes['quantity'];
            $fueling->update($attributes);

            DB::commit();

            return $fueling;
        } catch (Exception $exception) {
            DB::rollBack();

            throw $exception;
        }
    }


    public function updateImport(Fueling $fueling, array $attributes)
    {
        try {
            DB::beginTransaction();

            $attributes['source'] = FuelingSourceEnum::MANUALLY;
            $attributes['amount'] = $attributes['unit_price'] * $attributes['quantity'];
            $fueling->update($attributes);
            $fueling->valid = $fueling->validStatus->passes();
            $fueling->save();
            DB::commit();

            return $fueling;
        } catch (Exception $exception) {
            DB::rollBack();

            throw $exception;
        }
    }

}
