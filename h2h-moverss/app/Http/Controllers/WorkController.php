<?php

namespace App\Http\Controllers;

use App\Http\Requests\Order\WorkRequest;
use Illuminate\Http\{JsonResponse, Request};
use App\Models\{Audit, Order\Work, PeakDate, Order, Settings};
use Auth, Exception;

/**
 * Manage Works for Order.
 * todo перенести в директорию order
 */
class WorkController extends Controller
{
    /**
     * Edit or Create Work.
     * @param WorkRequest $request
     * @return JsonResponse
     *
     * test @see \Tests\Feature\Orders\Works\SaveTest (дополнить)
     */
    public function save(WorkRequest $request): JsonResponse
    {
        $validated = $request->validated();

//        dd($validated);
        $order = Order::withWorksFormat()->with('status')->findOrFail($validated['order_id']);

        $work = isset($validated['id']) ? $order->works->where('id', $validated['id'])->first() : new Work();

        $work->start_date = $validated['start_date'];
        $work->start_time = $validated['start_time'] ?: null;
        $work->start_time_to = $validated['start_time_to'] ?: null;
        $work->duration = $validated['duration'] ?? null;
        $work->trucks = $validated['trucks'];
        $work->employees = $validated['employees'];

        if ($order->status->enable_dispatch) {
            $work->in_dispatch = 1;
        }

        if (!isset($work->notes) || $work->notes !== $validated['notes']) {
            $work->notes = $validated['notes'];
            $work->notes_by = Auth::user()->id;
            $work->notes_created_at = now();
        }

        try {
            $oldTypes = $work->workTypes
                ->pluck('title')->toArray();

            if (isset($validated['id'])) {
                $work->save();

                $work->auditSync('workTypes', $validated['work_types_checked']);

            } else {
                $work->order_id = $validated['order_id'];
                $work->save();

                $work->auditSync('workTypes', $validated['work_types_checked']);
            }

            $workClone = clone $work;
            $workClone->refresh();
            $newTypes = $workClone->workTypes->pluck('title')->toArray();

            $audit = Audit::query()
                ->where('event', Audit::EVENT_SYNC)
                ->where('auditable_type', Work::MORPH_NAME)
                ->where('auditable_id', $work->id)
                ->latest()
                ->first();

            if($audit){
                $audit->new_values = array_merge($audit->new_values, ['custom_work_types' => $newTypes]);
                $audit->old_values = array_merge($audit->old_values, ['custom_work_types' => $oldTypes]);
                $audit->save();
            }


        } catch (Exception $e) {
            return response()
                ->json([
                    'success' => false,
                    'msg' => $e->getMessage()
                ]);
        }

        $order = Order::withWorksFormat()->findOrFail($validated['order_id']);

        return response()
            ->json([
                'success' => true,
                'records' => $order->works
            ]);
    }

    /**
     * Remove assignments on work (Trucks and Employees).
     * @param Request $request
     * @return JsonResponse
     */
    public function removeAssignments(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'id' => 'required|integer|exists:orders_works,id',
            'order_id' => 'required|integer|exists:orders,id',
        ]);

        $work = Work::find($validated['id']);
        $work->dispatchTrucks()->delete();
        $work->dispatchEmployees()->delete();

        $order = Order::withWorksFormat()->findOrFail($validated['order_id']);

        return response()
            ->json([
                'success' => true,
                'records' => $order->works,
            ]);
    }

    /**
     * Remove Work.
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function remove(Request $request): JsonResponse
    {
        // FIXME If the work is already marked up on tracks, then display a notification
        $validated = $request->validate([
            'id' => 'required|integer|exists:orders_works,id',
            'order_id' => 'required|integer|exists:orders,id',
        ]);

        $order = Order::withWorksFormat()->findOrFail($validated['order_id']);

        $work = $order->works->where('id', $validated['id'])->first();

        $work->workTypes()->detach();
        $work->delete();

        $order = $order->fresh(['works', 'works.workTypes']);

        return response()
            ->json([
                'success' => true,
                'records' => $order->works
            ]);
    }

    /**
     * Get peak dates and days of the week.
     * @return JsonResponse
     */
    public function peaksRecords(): JsonResponse
    {
        return response()
            ->json([
                'success' => true,
                'records' => PeakDate::get(),
                'weeklyPeaks' => Settings::whereName('peak_week_days')->whereDivisionId(session('division')['id'])->first('miscs')->miscs
            ]);
    }
}
