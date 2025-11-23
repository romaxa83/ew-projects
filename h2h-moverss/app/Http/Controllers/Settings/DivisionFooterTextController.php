<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\Division;
use App\Models\Order\CustomerPage;
use Illuminate\Http\{JsonResponse, Request};

class DivisionFooterTextController extends Controller
{
    public function index(Request $request, Division $division)
    {
        if ($request->ajax()) {
            return response()
                ->json([
                    'success' => true,
                    'records' => $division->with(['afterwords'])->get(['id', 'title']),
                ]);
        }

        return view('layouts.render.with-container', [
            'component' => 'settings-divisions-footer-texts',
            'title' => 'Manage Customer Page Texts',
            'h2' => 'Manage Branches Texts',
        ]);
    }

    public function store(Request $request, CustomerPage $_record): JsonResponse
    {
        $validated = $request->validate([
            'record.id' => 'required|integer|exists:divisions,id',
            'record.afterwords.*.id' => 'required|integer|exists:customer_pages,id',
            'record.afterwords.*.title' => 'required|string|max:80',
            'record.afterwords.*.text' => 'required|string|max:16777000',
        ]);

        foreach ($validated['record']['afterwords'] as $v) {
            $_record->where('id', $v['id'])->update($v);
        }

        return response()
            ->json([
                'success' => true,
            ]);
    }
}
