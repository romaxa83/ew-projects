<?php

namespace App\Http\Controllers\Company;

use App\DataTables\Company\TruckRecordsDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Company\TrucksRequest;
use App\Models\Division;
use App\Models\Partners\Partner;
use App\Models\Truck\Truck;
use App\Repositories\Partners\PartnerRepository;
use Auth;
use DB;
use Exception;
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Validator;

/**
 * Managing Trucks.
 */
class TrucksController extends Controller
{
    public function __construct(protected PartnerRepository $partnerRepo)
    {}

    /**
     * All info about Truck record.
     * @param  Request  $request
     * @return JsonResponse
     */
    public function ajaxInfo(Request $request): JsonResponse
    {
        $record_id = (int) $request->route('id');
        $record = Truck::records()->findOrFail($record_id);

        return response()
            ->json([
                'success' => true,
                'record' => $record,
                'divisions' => Division::get(['id', 'title'])->keyBy('id'),
                'partners' => Partner::get(['id', 'name'])->keyBy('id'),
//                'partners' => $this->partnerRepo->forSelect(),
            ]);
    }

    /**
     * Saving Truck data.
     * @param  TrucksRequest  $request
     * @return JsonResponse
     * @throws \Throwable
     *
     * test @see \Tests\Feature\Trucks\SaveTest (дополнить)
     */
    public function save(TrucksRequest $request): JsonResponse
    {
        $validated = $request->validated();
//dd($validated);
        $record = Truck::records()->findOrFail($validated['id']);
//
        try {
            $record->fill($validated);

            foreach ($validated['notes'] ?? [] as $k => $note) {
                if($note['id']){
                    $userId = $record->notes->where('id', $note['id'])->first()->user_id;
                } else {
                    $userId = Auth::user()->id;
                }
                $validated['notes'][$k]['user_id'] = $userId;
            }

            $changed = $record->isDirty() ? 1 : 0;
            DB::transaction(function () use ($record, $validated, &$changed) {
                $changed += $record->updateRelations('notes', $validated['notes'] ?? []);
                $changed += $record->updateRelations('busyDates', $validated['busy_dates'] ?? []);

                if (!$record->busyWeeksDays || (count($validated['busy_weeks_days']['miscs']) !== count($record->busyWeeksDays->miscs))) {
                    $record->busyWeeksDays()
                        ->updateOrCreate(
                            [
                                'truck_id' => $record->id,
                            ],
                            [
                                'user_id' => Auth::user()->id,
                                'miscs' => $validated['busy_weeks_days']['miscs'],
                            ]);
                    $changed++;
                }

                if ($record->isDirty()) {
                    $record->save();
                }
            });
        } catch (Exception $e) {
            return response()
                ->json([
                    'success' => false,
                    'msg' => $e->getMessage()
                ]);
        }

        $record = Truck::records()->find($validated['id']);

        return response()
            ->json([
                'success' => true,
                'msg' => $changed ? 'Truck changed' : 'Changed nothing',
                'record' => $record,
            ]);
    }

    /**
     * Create Truck with base fields (title, nickname, license plate, year).
     * @param  Request  $request
     * @param  Truck  $record
     * @return RedirectResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function createEmpty(Request $request, Truck $record): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:50',
            'nickname' => 'required|string|max:50',
            'l_plate' => 'nullable|string|max:20',
            'year' => 'nullable|string|max:4',
        ]);

        if ($validator->fails()) {
            return redirect()->route('company.trucks.records')
                ->withErrors($validator)
                ->withInput();
        }

        $validated = $validator->validated();
        $validated['active'] = 1;
        $validated['division_ids'][] = request()->session()->get('division.id');
        $new = $record->create($validated);

        return redirect()
            ->route('company.trucks.record', ['id' => $new->id]);
    }

    /**
     * Get records for DT.
     * @param  TruckRecordsDataTable  $dataTable
     * @return mixed
     */
    public function records(TruckRecordsDataTable $dataTable)
    {
        return $dataTable->render('layouts.company.trucks.records');
    }

}
