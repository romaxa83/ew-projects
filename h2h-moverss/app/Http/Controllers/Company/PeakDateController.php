<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Http\Requests\Company\PeakDateRequest;
use Illuminate\Http\JsonResponse;
use App\Models\{PeakDate, Settings};

/**
 * Manage Peak Dates.
 */
class PeakDateController extends Controller
{
    /**
     * Get all info for AJAX.
     * @return JsonResponse
     */
    public function ajaxInfo(): JsonResponse
    {



        return response()
            ->json([
                'success' => true,
                'records' => PeakDate::get(),
                'peak_week_days' => Settings::whereName('peak_week_days')->whereDivisionId(session('division')['id'])->first()->miscs,
                'types' => PeakDate\Type::get(['id', 'title', 'color'])->keyBy('id'),
            ]);
    }

    /**
     * Saving data.
     * @param  PeakDateRequest  $request
     * @return JsonResponse
     */
    public function save(PeakDateRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $all_records = PeakDate::get();

        $peak = Settings::whereName('peak_week_days')->whereDivisionId(session('division')['id'])->first();
        $peak->miscs = (array) $validated['peakWeekDays'];
        if ($peak->isDirty()) {
            $peak->save();
        }

        $ids = [];
        foreach ($validated['records'] as $v) {
            $record = $all_records->find($v['id']);
            if ($record) {
                if ($record->type_id !== $v['type_id'] ||
                    $record->description !== $v['description'] ||
                    $record->date !== $v['date']) {
                    // Update if changed
                    $record->type_id = $v['type_id'];
                    $record->description = $v['description'];
                    $record->date = $v['date'];
                    $record->save();
                }
            } else {
                // Create
                $record = new PeakDate();
                $record->type_id = $v['type_id'];
                $record->description = $v['description'];
                $record->date = $v['date'];
                $record->save();
            }
            $ids[] = $record->id;
        }

        PeakDate::whereNotIn('id', $ids)->delete();

        return response()
            ->json([
                'success' => true,
                'records' => PeakDate::get(),
            ]);
    }
}
