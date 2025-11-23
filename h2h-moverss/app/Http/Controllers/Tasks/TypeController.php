<?php

namespace App\Http\Controllers\Tasks;

use App\Http\Controllers\Controller;
use App\Models\Tasks\Type;
use Illuminate\Http\{JsonResponse, Request};

/**
 * Manage Tasks Types
 */
class TypeController extends Controller
{
    /**
     * Get all records.
     * @param  Type  $type
     * @return JsonResponse
     */
    public function records(Type $type): JsonResponse
    {
        return response()
            ->json([
                'success' => true,
                'records' => $type->get(),
            ]);
    }

    /**
     * Save records + validate (Update or Create).
     * @param  Request  $request
     * @param  Type  $type
     * @return JsonResponse
     */
    public function save(Request $request, Type $type): JsonResponse
    {
        $request->validate([
            'records.*.title' => 'required|string|min:2|max:50',
            'records.*.class' => 'nullable|string|max:30',
            'records.*.color' => 'nullable|string|max:7',
            'records.*.sort' => 'required|integer|min:1|max:50',
            'records.*.active' => 'required|nullable|boolean',
        ]);

        foreach ($request->get('records', []) as $v) {
            $type->updateOrCreate([
                'id' => $v['id'],
            ], $v);
        }

        return response()
            ->json([
                'success' => true,
                'msg' => 'Successfully updated',
            ]);
    }
}
