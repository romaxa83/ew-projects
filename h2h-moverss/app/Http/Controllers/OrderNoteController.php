<?php

namespace App\Http\Controllers;

use App\Http\Requests\Order\Notes\CreateRequest as NotesRequest;
use App\Services\Communications\FormatterService;
use App\Services\Communications\RecordCreateService;
use App\Services\Communications\RecordRemoveService;
use App\Traits\ResponseFormatter;
use Illuminate\Http\{JsonResponse, Request};
use App\Models\Order\Notes;
use Auth, Exception;

/**
 * Manage Order Notes.
 */
class OrderNoteController extends Controller
{
    use ResponseFormatter;

    public function __construct(public FormatterService $formatterService)
    {}

    /**
     * Get all notes for order.
     * @param Notes $notes
     * @param Request $request
     * @return JsonResponse
     */
    public function records(Notes $notes, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_id' => 'required|integer|exists:orders,id',
        ]);

        $records = $notes
            ->where('order_id', $validated['order_id'])
            ->get();

        return response()
            ->json([
                'success' => true,
                'records' => $records
            ]);
    }

    /**
     * Add new note.
     * @param NotesRequest $request
     * @param Notes $notes
     * @return JsonResponse
     *
     * test @see \Tests\Feature\Orders\Notes\SaveTest (дополнить тесты)
     */
    public function save(NotesRequest $request, Notes $notes): JsonResponse
    {
        $validated = $request->validated();

        try {
            $record = $notes;
            $record->order_id = $validated['order_id'];
            $record->user_id = Auth::user()->id;
            $record->text = strip_tags($validated['text'], '<br/>');
            $record->is_pinned = !empty($validated['is_pinned']) ? 1 : 0;
            $record->save();

            $rec = RecordCreateService::handler($record);

            if (!empty($validated['returnFormat']) && $validated['returnFormat'] == 'communicationPanel') {
                $record->load('author:id,name', 'author.employee:id,name,l_name');
                //->with('author:id,name', 'author.employee:id,name,l_name')
                $response = ['record' => $this->formatterService->recForMainPanelBase($rec)];
            } else {
                $response = ['records' => $notes->where('order_id', $validated['order_id'])->get()];
            }
            $response['success'] = true;
        } catch (Exception $e) {
            return response()
                ->json([
                    'success' => false,
                    'msg' => $e->getMessage()
                ]);
        }

        return response()
            ->json($response);
    }

    /**
     * Update note record.
     * @param Request $request
     * @return JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        try {
            $response = ['success' => false];
            $validated = $request->validate([
                'id' => 'required|integer|exists:orders_notes,id',
                'text' => 'required|string',
            ]);

            $Note = Notes::findOrFail($validated['id']);
            $User = Auth::user();
            if (!$User->isAdmin() && $Note->user_id != $User->id) {
                throw new Exception('You have no permissions to update this Note!');
            }
            $Note->text = $validated['text'];
            $Note->save();
            $response['record'] = $this->getCommunicationPanelFormat($Note);
            $response['success'] = true;
            //$Note->delete();
        } catch (Exception $e) {
            return response()
                ->json([
                    'success' => false,
                    'msg' => $e->getMessage()
                ]);
        }

        return response()
            ->json($response);
    }


    /**
     * Remove note record.
     * @param Request $request
     * @return JsonResponse
     *
     * test @see \Tests\Feature\Orders\Notes\RemoveTest
     */
    public function remove(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'id' => 'required|integer|exists:orders_notes,id',
            ]);

            $Note = Notes::findOrFail($validated['id']);
            $User = Auth::user();
            if (!$User->isAdmin() && $Note->user_id != $User->id) {
                throw new Exception('You have no permissions to remove this Note!');
            }

            RecordRemoveService::handler($Note);

            $Note->delete();
        } catch (Exception $e) {
            return response()
                ->json([
                    'success' => false,
                    'msg' => $e->getMessage()
                ]);
        }

        return response()
            ->json([
                'success' => true,
            ]);
    }
}
